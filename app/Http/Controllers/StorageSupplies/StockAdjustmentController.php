<?php

namespace App\Http\Controllers\StorageSupplies;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemStockBalance;
use App\Models\Lookup;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentBatch;
use App\Models\StockAdjustmentBatchAllocation;
use App\Models\StockAdjustmentItem;
use App\Models\StockBatch;
use App\Models\StockLedger;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StockAdjustmentController extends Controller
{
    public function draftList(): View
    {
        return $this->nicePage('templates.storage_supplies.stock_adjustment.draft_list', 'storage-supplies.stock-adjustment.new', [
            'items' => StockAdjustment::with('officer')
                ->where('subdepartment_id', session('active_subdepartment_id'))
                ->where('status', 'draft')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return $this->nicePage('templates.storage_supplies.stock_adjustment.create', 'storage-supplies.stock-adjustment.new', [
            'nextAdjustmentNumberPreview' => (int) (StockAdjustment::max('id') ?? 0) + 1,
            'itemCategories' => Lookup::ofType('item_category')->orderBy('name')->get(),
        ]);
    }

    public function itemsPicker(Request $request): JsonResponse
    {
        $subdepartmentId = session('active_subdepartment_id');
        $reason = $request->query('reason');

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

        // Deduct mode only shows items with real stock to remove from; Add mode shows everything.
        $filtered = $reason === 'expired_dump_broken'
            ? $items->filter(fn ($item) => $balances->get($item->id, 0) > 0)->values()
            : $items;

        return response()->json($filtered->map(fn ($item) => [
            'id' => $item->id,
            'name' => $item->product_name,
            'uom' => $item->unitOfMeasure->name ?? '',
            'balance' => $balances->get($item->id, 0),
        ]));
    }

    private function validatePayload(Request $request, ?string $lockedReason = null): array
    {
        $reason = $lockedReason ?? $request->input('reason');

        $rules = [
            'adjustment_date' => 'required|date',
            'description' => 'required|string|max:255',
            'submit' => 'nullable|boolean',
        ];

        if (! $lockedReason) {
            $rules['reason'] = 'required|in:add_stock_balance,expired_dump_broken';
        }

        if ($reason === 'add_stock_balance') {
            $rules['items'] = 'required|array|min:1';
            $rules['items.*.item_id'] = 'required|integer|exists:tbl_items,id';
            $rules['items.*.batches'] = 'required|array|min:1';
            $rules['items.*.batches.*.batch_number'] = 'required|string|max:100';
            $rules['items.*.batches.*.units'] = 'required|integer|min:1';
            $rules['items.*.batches.*.items_per_unit'] = 'required|integer|min:1';
            $rules['items.*.batches.*.buying_price'] = 'required|numeric|min:0';
            $rules['items.*.batches.*.manufacture_date'] = 'required|date|before_or_equal:today';
            $rules['items.*.batches.*.expiry_date'] = 'required|date|after_or_equal:today';
            $rules['items.*.batches.*.received_date'] = 'required|date';
        } else {
            $rules['items'] = 'required|array|min:1';
            $rules['items.*.item_id'] = 'required|integer|exists:tbl_items,id';
            $rules['items.*.quantity'] = 'required|integer|min:1';
        }

        $validated = $request->validate($rules);
        $validated['reason'] = $reason;
        $validated['submit'] = (bool) ($validated['submit'] ?? false);

        if ($reason === 'expired_dump_broken') {
            $balances = ItemStockBalance::where('subdepartment_id', session('active_subdepartment_id'))
                ->whereIn('item_id', collect($validated['items'])->pluck('item_id'))
                ->pluck('quantity_balance', 'item_id');

            foreach ($validated['items'] as $line) {
                $available = $balances->get($line['item_id'], 0);
                if ($line['quantity'] > $available) {
                    $itemName = Item::find($line['item_id'])?->product_name ?? "Item #{$line['item_id']}";
                    abort(422, "Quantity for \"{$itemName}\" ({$line['quantity']}) exceeds current store balance ({$available}).");
                }
            }
        }

        return $validated;
    }

    private function persist(array $data, ?StockAdjustment $adjustment): StockAdjustment
    {
        $status = $data['submit'] ? 'pending_approval' : 'draft';

        return DB::transaction(function () use ($data, $adjustment, $status) {
            if ($adjustment) {
                $adjustment->update([
                    'adjustment_date' => $data['adjustment_date'],
                    'description' => $data['description'],
                    'status' => $status,
                    'submitted_by_user_id' => $status === 'pending_approval' ? session('user_id') : $adjustment->submitted_by_user_id,
                    'submitted_at' => $status === 'pending_approval' ? now() : $adjustment->submitted_at,
                ]);
                $adjustment->items()->delete();
            } else {
                $adjustment = StockAdjustment::create([
                    'adjustment_date' => $data['adjustment_date'],
                    'subdepartment_id' => session('active_subdepartment_id'),
                    'officer_user_id' => session('user_id'),
                    'description' => $data['description'],
                    'reason' => $data['reason'],
                    'status' => $status,
                    'submitted_by_user_id' => $status === 'pending_approval' ? session('user_id') : null,
                    'submitted_at' => $status === 'pending_approval' ? now() : null,
                ]);
            }

            foreach ($data['items'] as $line) {
                if ($data['reason'] === 'add_stock_balance') {
                    $totalQty = collect($line['batches'])->sum(fn ($b) => $b['units'] * $b['items_per_unit']);

                    $adjustmentItem = StockAdjustmentItem::create([
                        'adjustment_id' => $adjustment->id, 'item_id' => $line['item_id'], 'quantity' => $totalQty,
                    ]);

                    foreach ($line['batches'] as $batch) {
                        StockAdjustmentBatch::create([
                            'adjustment_item_id' => $adjustmentItem->id,
                            'batch_number' => $batch['batch_number'],
                            'units' => $batch['units'], 'items_per_unit' => $batch['items_per_unit'],
                            'quantity' => $batch['units'] * $batch['items_per_unit'],
                            'buying_price' => $batch['buying_price'],
                            'manufacture_date' => $batch['manufacture_date'],
                            'expiry_date' => $batch['expiry_date'],
                            'received_date' => $batch['received_date'],
                        ]);
                    }
                } else {
                    StockAdjustmentItem::create([
                        'adjustment_id' => $adjustment->id, 'item_id' => $line['item_id'], 'quantity' => $line['quantity'],
                    ]);
                }
            }

            return $adjustment;
        });
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatePayload($request);
        $adjustment = $this->persist($data, null);

        return response()->json(['success' => true, 'id' => $adjustment->id]);
    }

    public function edit(StockAdjustment $adjustment): View
    {
        abort_unless($adjustment->status === 'draft', 403, 'Only draft adjustments can be edited.');
        abort_unless($adjustment->subdepartment_id === session('active_subdepartment_id'), 403);

        $adjustment->load('items.item.unitOfMeasure', 'items.batches');

        $balances = ItemStockBalance::where('subdepartment_id', session('active_subdepartment_id'))
            ->whereIn('item_id', $adjustment->items->pluck('item_id'))
            ->pluck('quantity_balance', 'item_id');

        return $this->nicePage('templates.storage_supplies.stock_adjustment.edit', 'storage-supplies.stock-adjustment.new', [
            'adjustment' => $adjustment,
            'balances' => $balances,
            'itemCategories' => Lookup::ofType('item_category')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, StockAdjustment $adjustment): JsonResponse
    {
        abort_unless($adjustment->status === 'draft', 403, 'Only draft adjustments can be edited.');

        $data = $this->validatePayload($request, $adjustment->reason);
        $this->persist($data, $adjustment);

        return response()->json(['success' => true]);
    }

    public function submit(StockAdjustment $adjustment): JsonResponse
    {
        abort_unless($adjustment->status === 'draft', 403, 'Only draft adjustments can be submitted.');
        abort_unless($adjustment->subdepartment_id === session('active_subdepartment_id'), 403);
        abort_unless($adjustment->items()->exists(), 422, 'Add at least one item before submitting.');

        $adjustment->update(['status' => 'pending_approval', 'submitted_by_user_id' => session('user_id'), 'submitted_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function approveList(): View
    {
        return $this->nicePage('templates.storage_supplies.stock_adjustment.approve_list', 'storage-supplies.stock-adjustment.approve', [
            'items' => StockAdjustment::with('officer')
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

    public function approve(Request $request, StockAdjustment $adjustment): JsonResponse
    {
        abort_unless($adjustment->status === 'pending_approval', 403, 'Only adjustments pending approval can be approved.');
        abort_unless($adjustment->subdepartment_id === session('active_subdepartment_id'), 403);

        $credentials = $request->validate(['username' => 'required|string', 'password' => 'required|string']);
        $approver = User::where('email', $credentials['username'])->first();

        if (! $approver || ! Hash::check($credentials['password'], $approver->password)) {
            return response()->json(['message' => 'Invalid username or password.'], 422);
        }

        if (! $approver->hasApprovalPermission('store_adjustment_approval')) {
            return response()->json(['message' => 'This user is not authorized to approve Stock Adjustments.'], 403);
        }

        $adjustment->load('items.item', 'items.batches');
        $subId = $adjustment->subdepartment_id;

        DB::transaction(function () use ($adjustment, $approver, $subId) {
            foreach ($adjustment->items as $adjustmentItem) {
                if ($adjustment->reason === 'add_stock_balance') {
                    foreach ($adjustmentItem->batches as $batch) {
                        $balance = ItemStockBalance::firstOrCreate(
                            ['item_id' => $adjustmentItem->item_id, 'subdepartment_id' => $subId],
                            ['quantity_balance' => 0]
                        );
                        $newBalance = $balance->quantity_balance + $batch->quantity;
                        $balance->update(['quantity_balance' => $newBalance]);

                        $stockBatch = StockBatch::create([
                            'item_id' => $adjustmentItem->item_id, 'subdepartment_id' => $subId,
                            'batch_number' => $batch->batch_number, 'manufacture_date' => $batch->manufacture_date,
                            'expiry_date' => $batch->expiry_date, 'buying_price' => $batch->buying_price,
                            'quantity_received' => $batch->quantity, 'quantity_remaining' => $batch->quantity,
                            'source_type' => 'stock_adjustment', 'source_id' => $adjustment->id,
                            'received_date' => $batch->received_date,
                        ]);

                        StockLedger::create([
                            'item_id' => $adjustmentItem->item_id, 'subdepartment_id' => $subId,
                            'movement_type' => 'adjustment_add', 'reference_type' => 'stock_adjustment', 'reference_id' => $adjustment->id,
                            'quantity_in' => $batch->quantity, 'quantity_out' => 0, 'balance_after' => $newBalance,
                            'stock_batch_id' => $stockBatch->id, 'created_by_user_id' => $approver->id, 'moved_at' => now(),
                        ]);
                    }
                } else {
                    $result = $this->planFefo($adjustmentItem->item_id, $subId, $adjustmentItem->quantity);

                    if ($result['shortfall'] > 0) {
                        abort(422, "Insufficient batch stock for \"{$adjustmentItem->item->product_name}\" — {$result['shortfall']} unit(s) short.");
                    }

                    foreach ($result['plan'] as $allocation) {
                        $batch = $allocation['batch'];
                        $batch->decrement('quantity_remaining', $allocation['quantity']);

                        $balance = ItemStockBalance::where('item_id', $adjustmentItem->item_id)
                            ->where('subdepartment_id', $subId)->lockForUpdate()->first();
                        $newBalance = $balance->quantity_balance - $allocation['quantity'];
                        $balance->update(['quantity_balance' => $newBalance]);

                        StockLedger::create([
                            'item_id' => $adjustmentItem->item_id, 'subdepartment_id' => $subId,
                            'movement_type' => 'adjustment_deduct', 'reference_type' => 'stock_adjustment', 'reference_id' => $adjustment->id,
                            'quantity_in' => 0, 'quantity_out' => $allocation['quantity'], 'balance_after' => $newBalance,
                            'grn_batch_id' => null, 'created_by_user_id' => $approver->id, 'moved_at' => now(),
                        ]);

                        StockAdjustmentBatchAllocation::create([
                            'adjustment_item_id' => $adjustmentItem->id,
                            'stock_batch_id' => $batch->id,
                            'quantity_allocated' => $allocation['quantity'],
                        ]);
                    }
                }
            }

            $adjustment->update(['status' => 'approved', 'approved_by_user_id' => $approver->id, 'approved_at' => now()]);
        });

        return response()->json(['success' => true]);
    }

    public function previousList(): View
    {
        return $this->nicePage('templates.storage_supplies.stock_adjustment.previous_list', 'storage-supplies.stock-adjustment.previous', [
            'items' => StockAdjustment::with('officer', 'approvedBy')
                ->where('subdepartment_id', session('active_subdepartment_id'))
                ->where('status', 'approved')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function preview(StockAdjustment $adjustment): View
    {
        $adjustment->load(['items.item.unitOfMeasure', 'items.batches', 'subdepartment.department.branch.company', 'officer', 'approvedBy']);
        $branch = $adjustment->subdepartment?->department?->branch;

        return view('templates.storage_supplies.stock_adjustment.preview', [
            'adjustment' => $adjustment, 'branch' => $branch, 'company' => $branch?->company,
        ]);
    }
}