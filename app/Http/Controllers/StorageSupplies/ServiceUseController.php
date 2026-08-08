<?php

namespace App\Http\Controllers\StorageSupplies;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemStockBalance;
use App\Models\Lookup;
use App\Models\ServiceUse;
use App\Models\ServiceUseItem;
use App\Models\StockBatch;
use App\Models\StockLedger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ServiceUseController extends Controller
{
    public function create(): View
    {
        return $this->nicePage('templates.storage_supplies.service_use.create', 'storage-supplies.service-use.new', [
            'nextDocumentNumberPreview' => (int) (ServiceUse::max('id') ?? 0) + 1,
            'itemCategories' => Lookup::ofType('item_category')->orderBy('name')->get(),
        ]);
    }

    public function itemsPicker(Request $request): JsonResponse
    {
        $subdepartmentId = session('active_subdepartment_id');

        $items = Item::query()
            ->with('unitOfMeasure')
            ->where('status', 'active')
            ->when($request->query('category_id'), fn ($q, $categoryId) => $q->where('item_category_id', $categoryId))
            ->when($request->query('search'), fn ($q, $search) => $q->where('product_name', 'ilike', "%{$search}%"))
            ->orderBy('product_name')
            ->limit(100)
            ->get(['id', 'product_name', 'unit_of_measure_id']);

        $balances = ItemStockBalance::where('subdepartment_id', $subdepartmentId)
            ->whereIn('item_id', $items->pluck('id'))
            ->pluck('quantity_balance', 'item_id');

        return response()->json($items->filter(fn ($item) => $balances->get($item->id, 0) > 0)->values()->map(fn ($item) => [
            'id' => $item->id,
            'name' => $item->product_name,
            'uom' => $item->unitOfMeasure->name ?? '',
            'balance' => $balances->get($item->id, 0),
        ]));
    }

    private function planFefo(int $itemId, int $subdepartmentId, int $quantityNeeded): array
    {
        $batches = StockBatch::availableFefo($itemId, $subdepartmentId)->get();
        $plan = [];
        $remaining = $quantityNeeded;

        foreach ($batches as $batch) {
            if ($remaining <= 0) break;

            $take = min($remaining, $batch->quantity_remaining);
            $plan[] = ['batch' => $batch, 'quantity' => $take];
            $remaining -= $take;
        }

        return ['plan' => $plan, 'shortfall' => max($remaining, 0)];
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'requisition_date' => 'required|date',
            'reason' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:tbl_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $subdepartmentId = session('active_subdepartment_id');

        $balances = ItemStockBalance::where('subdepartment_id', $subdepartmentId)
            ->whereIn('item_id', collect($data['items'])->pluck('item_id'))
            ->pluck('quantity_balance', 'item_id');

        foreach ($data['items'] as $line) {
            $available = $balances->get($line['item_id'], 0);
            if ($line['quantity'] > $available) {
                $itemName = Item::find($line['item_id'])?->product_name ?? "Item #{$line['item_id']}";
                abort(422, "Quantity for \"{$itemName}\" ({$line['quantity']}) exceeds current store balance ({$available}).");
            }
        }

        $serviceUse = DB::transaction(function () use ($data, $subdepartmentId) {
            $serviceUse = ServiceUse::create([
                'requisition_date' => $data['requisition_date'],
                'subdepartment_id' => $subdepartmentId,
                'officer_user_id' => session('user_id'),
                'reason' => $data['reason'],
            ]);

            foreach ($data['items'] as $line) {
                $item = Item::findOrFail($line['item_id']);

                $result = $this->planFefo($line['item_id'], $subdepartmentId, $line['quantity']);

                if ($result['shortfall'] > 0) {
                    abort(422, "Insufficient batch stock for \"{$item->product_name}\" — {$result['shortfall']} unit(s) short.");
                }

                ServiceUseItem::create([
                    'service_use_id' => $serviceUse->id,
                    'item_id' => $line['item_id'],
                    'quantity' => $line['quantity'],
                ]);

                foreach ($result['plan'] as $allocation) {
                    $batch = $allocation['batch'];
                    $batch->decrement('quantity_remaining', $allocation['quantity']);

                    $balance = ItemStockBalance::where('item_id', $line['item_id'])
                        ->where('subdepartment_id', $subdepartmentId)
                        ->lockForUpdate()
                        ->first();
                    $newBalance = $balance->quantity_balance - $allocation['quantity'];
                    $balance->update(['quantity_balance' => $newBalance]);

                    StockLedger::create([
                        'item_id' => $line['item_id'],
                        'subdepartment_id' => $subdepartmentId,
                        'movement_type' => 'service_use',
                        'reference_type' => 'service_use',
                        'reference_id' => $serviceUse->id,
                        'quantity_in' => 0,
                        'quantity_out' => $allocation['quantity'],
                        'balance_after' => $newBalance,
                        'grn_batch_id' => null,
                        'created_by_user_id' => session('user_id'),
                        'moved_at' => now(),
                    ]);
                }
            }

            return $serviceUse;
        });

        return response()->json(['success' => true, 'id' => $serviceUse->id]);
    }

    public function previousList(): View
    {
        return $this->nicePage('templates.storage_supplies.service_use.previous_list', 'storage-supplies.service-use.previous', [
            'items' => ServiceUse::with(['subdepartment', 'officer'])
                ->where('subdepartment_id', session('active_subdepartment_id'))
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function preview(ServiceUse $serviceUse): View
    {
        $serviceUse->load(['items.item.unitOfMeasure', 'subdepartment.department.branch.company', 'officer']);
        $branch = $serviceUse->subdepartment?->department?->branch;

        return view('templates.storage_supplies.service_use.preview', [
            'serviceUse' => $serviceUse, 'branch' => $branch, 'company' => $branch?->company,
        ]);
    }
}