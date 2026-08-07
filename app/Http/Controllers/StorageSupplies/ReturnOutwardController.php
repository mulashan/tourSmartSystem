<?php

namespace App\Http\Controllers\StorageSupplies;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemStockBalance;
use App\Models\Lookup;
use App\Models\ReturnOutward;
use App\Models\ReturnOutwardBatchAllocation;
use App\Models\ReturnOutwardItem;
use App\Models\StockBatch;
use App\Models\StockLedger;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ReturnOutwardController extends Controller
{
    public function draftList(): View
    {
        return $this->nicePage('templates.storage_supplies.return_outward.draft_list', 'storage-supplies.return-outward.new', [
            'items' => ReturnOutward::with(['supplier', 'postedBy'])
                ->where('subdepartment_id', session('active_subdepartment_id'))
                ->where('status', 'draft')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return $this->nicePage('templates.storage_supplies.return_outward.create', 'storage-supplies.return-outward.new', [
            'nextDocumentNumberPreview' => (int) (ReturnOutward::max('id') ?? 0) + 1,
            'suppliers' => Supplier::orderBy('supplier_name')->get(),
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
            ->when($request->query('search'), fn ($q, $search) => $q->where('product_name', 'like', "%{$search}%"))
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

    private function validatePayload(Request $request, ?int $lockedSupplierId = null): array
    {
        $rules = [
            'transaction_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:tbl_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'description' => 'required|string|max:255',
        ];

        if (! $lockedSupplierId) {
            $rules['supplier_id'] = 'required|integer|exists:tbl_suppliers,id';
        }

        $validated = $request->validate($rules);
        $validated['supplier_id'] = $lockedSupplierId ?? $validated['supplier_id'];

        $balances = ItemStockBalance::where('subdepartment_id', session('active_subdepartment_id'))
            ->whereIn('item_id', collect($validated['items'])->pluck('item_id'))
            ->pluck('quantity_balance', 'item_id');

        foreach ($validated['items'] as $line) {
            $available = $balances->get($line['item_id'], 0);

            if ($line['quantity'] > $available) {
                $itemName = Item::find($line['item_id'])?->product_name ?? "Item #{$line['item_id']}";
                abort(422, "Quantity to return for \"{$itemName}\" ({$line['quantity']}) exceeds current store balance ({$available}).");
            }
        }

        return $validated;
    }

    private function persist(array $data, ?ReturnOutward $return): ReturnOutward
    {
        return DB::transaction(function () use ($data, $return) {
            if ($return) {
                $return->update(['transaction_date' => $data['transaction_date'],'description' => $data['description']]);
                $return->items()->delete();
            } else {
                $return = ReturnOutward::create([
                    'transaction_date' => $data['transaction_date'],
                    'subdepartment_id' => session('active_subdepartment_id'),
                    'description' => $data['description'],
                    'supplier_id' => $data['supplier_id'],
                    'posted_by_user_id' => session('user_id'),
                    'status' => 'draft',
                ]);
            }

            foreach ($data['items'] as $line) {
                $return->items()->create(['item_id' => $line['item_id'], 'quantity' => $line['quantity']]);
            }

            return $return;
        });
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatePayload($request);
        $return = $this->persist($data, null);

        return response()->json(['success' => true, 'id' => $return->id]);
    }

    public function edit(ReturnOutward $return): View
    {
        abort_unless($return->status === 'draft', 403, 'Only draft returns can be edited.');
        abort_unless($return->subdepartment_id === session('active_subdepartment_id'), 403);

        $return->load('items.item.unitOfMeasure', 'supplier');

        $balances = ItemStockBalance::where('subdepartment_id', session('active_subdepartment_id'))
            ->whereIn('item_id', $return->items->pluck('item_id'))
            ->pluck('quantity_balance', 'item_id');

        return $this->nicePage('templates.storage_supplies.return_outward.edit', 'storage-supplies.return-outward.new', [
            'return' => $return,
            'balances' => $balances,
            'itemCategories' => Lookup::ofType('item_category')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, ReturnOutward $return): JsonResponse
    {
        abort_unless($return->status === 'draft', 403, 'Only draft returns can be edited.');

        $data = $this->validatePayload($request, $return->supplier_id);
        $this->persist($data, $return);

        return response()->json(['success' => true]);
    }

    public function submit(ReturnOutward $return): JsonResponse
    {
        abort_unless($return->status === 'draft', 403, 'Only draft returns can be submitted.');
        abort_unless($return->subdepartment_id === session('active_subdepartment_id'), 403);
        abort_unless($return->items()->exists(), 422, 'Add at least one item before submitting.');

        $return->update([
            'status' => 'pending_approval',
            'submitted_by_user_id' => session('user_id'),
            'submitted_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function approveList(): View
    {
        return $this->nicePage('templates.storage_supplies.return_outward.approve_list', 'storage-supplies.return-outward.approve', [
            'items' => ReturnOutward::with(['supplier', 'postedBy'])
                ->where('subdepartment_id', session('active_subdepartment_id'))
                ->where('status', 'pending_approval')
                ->orderByDesc('id')
                ->get(),
        ]);
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

    public function approve(Request $request, ReturnOutward $return): JsonResponse
    {
        abort_unless($return->status === 'pending_approval', 403, 'Only returns pending approval can be approved.');
        abort_unless($return->subdepartment_id === session('active_subdepartment_id'), 403);

        $credentials = $request->validate(['username' => 'required|string', 'password' => 'required|string']);
        $approver = User::where('email', $credentials['username'])->first();

        if (! $approver || ! Hash::check($credentials['password'], $approver->password)) {
            return response()->json(['message' => 'Invalid username or password.'], 422);
        }

        if (! $approver->hasApprovalPermission('return_outward_approval')) {
            return response()->json(['message' => 'This user is not authorized to approve Return Outward.'], 403);
        }

        $return->load('items.item');
        $subId = $return->subdepartment_id;

        DB::transaction(function () use ($return, $approver, $subId) {
            foreach ($return->items as $returnItem) {
                $result = $this->planFefo($returnItem->item_id, $subId, $returnItem->quantity);

                if ($result['shortfall'] > 0) {
                    abort(422, "Insufficient batch stock for \"{$returnItem->item->product_name}\" — {$result['shortfall']} unit(s) short.");
                }

                foreach ($result['plan'] as $allocation) {
                    $batch = $allocation['batch'];
                    $batch->decrement('quantity_remaining', $allocation['quantity']);

                    $balance = ItemStockBalance::where('item_id', $returnItem->item_id)
                        ->where('subdepartment_id', $subId)
                        ->lockForUpdate()
                        ->first();
                    $newBalance = $balance->quantity_balance - $allocation['quantity'];
                    $balance->update(['quantity_balance' => $newBalance]);

                    StockLedger::create([
                        'item_id' => $returnItem->item_id,
                        'subdepartment_id' => $subId,
                        'movement_type' => 'return_outward',
                        'reference_type' => 'return_outward',
                        'reference_id' => $return->id,
                        'quantity_in' => 0,
                        'quantity_out' => $allocation['quantity'],
                        'balance_after' => $newBalance,
                        'grn_batch_id' => null,
                        'created_by_user_id' => $approver->id,
                        'moved_at' => now(),
                    ]);

                    ReturnOutwardBatchAllocation::create([
                        'return_item_id' => $returnItem->id,
                        'stock_batch_id' => $batch->id,
                        'quantity_allocated' => $allocation['quantity'],
                    ]);
                }
            }

            $return->update(['status' => 'approved', 'approved_by_user_id' => $approver->id, 'approved_at' => now()]);
        });

        return response()->json(['success' => true]);
    }

    public function previousList(): View
    {
        return $this->nicePage('templates.storage_supplies.return_outward.previous_list', 'storage-supplies.return-outward.previous', [
            'items' => ReturnOutward::with(['supplier', 'postedBy', 'approvedBy'])
                ->where('subdepartment_id', session('active_subdepartment_id'))
                ->where('status', 'approved')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function preview(ReturnOutward $return): View
    {
        $return->load(['items.item.unitOfMeasure', 'subdepartment.department.branch.company', 'supplier', 'postedBy', 'approvedBy']);
        $branch = $return->subdepartment?->department?->branch;

        return view('templates.storage_supplies.return_outward.preview', [
            'return' => $return, 'branch' => $branch, 'company' => $branch?->company,
        ]);
    }
}