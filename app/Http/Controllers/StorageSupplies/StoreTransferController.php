<?php

namespace App\Http\Controllers\StorageSupplies;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemStockBalance;
use App\Models\Lookup;
use App\Models\StockBatch;
use App\Models\StockLedger;
use App\Models\StoreTransfer;
use App\Models\StoreTransferBatchAllocation;
use App\Models\StoreTransferItem;
use App\Models\Subdepartment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StoreTransferController extends Controller
{
    public function draftList(): View
    {
        return $this->nicePage('templates.storage_supplies.store_transfer.draft_list', 'storage-supplies.store-transfer.draft', [
            'items' => StoreTransfer::with(['toSubdepartment', 'createdBy'])
                ->where('from_subdepartment_id', session('active_subdepartment_id'))
                ->where('status', 'draft')->orderByDesc('id')->get(),
        ]);
    }

    public function create(): View
    {
        return $this->nicePage('templates.storage_supplies.store_transfer.create', 'storage-supplies.store-transfer.draft', [
            'toSubdepartments' => Subdepartment::where('Subdepartment_ID', '!=', session('active_subdepartment_id'))->orderBy('Subdepartment_Name')->get(),
            'itemCategories' => Lookup::ofType('item_category')->orderBy('name')->get(),
        ]);
    }

    public function itemsPicker(Request $request): JsonResponse
    {
        $subdepartmentId = session('active_subdepartment_id');

        $items = Item::query()->with('unitOfMeasure')->where('status', 'active')
            ->when($request->query('category_id'), fn ($q, $c) => $q->where('item_category_id', $c))
            ->when($request->query('search'), fn ($q, $s) => $q->where('product_name', 'like', "%{$s}%"))
            ->orderBy('product_name')->limit(100)->get(['id', 'product_name', 'unit_of_measure_id']);

        $balances = ItemStockBalance::where('subdepartment_id', $subdepartmentId)
            ->whereIn('item_id', $items->pluck('id'))->pluck('quantity_balance', 'item_id');

        return response()->json($items->filter(fn ($item) => $balances->get($item->id, 0) > 0)->values()->map(fn ($item) => [
            'id' => $item->id, 'name' => $item->product_name,
            'uom' => $item->unitOfMeasure->name ?? '', 'balance' => $balances->get($item->id, 0),
        ]));
    }

    private function validatePayload(Request $request, ?int $currentTransferId = null): array
    {
        $validated = $request->validate([
            'to_subdepartment_id' => 'required|integer|exists:tbl_subdepartment,Subdepartment_ID',
            'description' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:tbl_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validated['to_subdepartment_id'] == session('active_subdepartment_id')) {
            abort(422, 'Cannot transfer to the same store.');
        }

        $balances = ItemStockBalance::where('subdepartment_id', session('active_subdepartment_id'))
            ->whereIn('item_id', collect($validated['items'])->pluck('item_id'))->pluck('quantity_balance', 'item_id');

        foreach ($validated['items'] as $line) {
            $available = $balances->get($line['item_id'], 0);
            if ($line['quantity'] > $available) {
                $itemName = Item::find($line['item_id'])?->product_name ?? "Item #{$line['item_id']}";
                abort(422, "Transfer quantity for \"{$itemName}\" ({$line['quantity']}) exceeds current store balance ({$available}).");
            }
        }

        return $validated;
    }

    private function persist(array $data, ?StoreTransfer $transfer): StoreTransfer
    {
        return DB::transaction(function () use ($data, $transfer) {
            if ($transfer) {
                $transfer->update(['to_subdepartment_id' => $data['to_subdepartment_id'], 'description' => $data['description'] ?? null]);
                $transfer->items()->delete();
            } else {
                $transfer = StoreTransfer::create([
                    'from_subdepartment_id' => session('active_subdepartment_id'),
                    'to_subdepartment_id' => $data['to_subdepartment_id'],
                    'created_by_user_id' => session('user_id'),
                    'transfer_date' => now()->toDateString(),
                    'description' => $data['description'] ?? null,
                    'status' => 'draft',
                ]);
            }

            foreach ($data['items'] as $line) {
                $transfer->items()->create(['item_id' => $line['item_id'], 'quantity' => $line['quantity']]);
            }

            return $transfer;
        });
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatePayload($request);
        $transfer = $this->persist($data, null);
        return response()->json(['success' => true, 'id' => $transfer->id]);
    }

    public function edit(StoreTransfer $transfer): View
    {
        abort_unless($transfer->status === 'draft', 403, 'Only draft transfers can be edited.');
        abort_unless($transfer->from_subdepartment_id === session('active_subdepartment_id'), 403);

        $transfer->load('items.item.unitOfMeasure', 'toSubdepartment');

        $balances = ItemStockBalance::where('subdepartment_id', session('active_subdepartment_id'))
            ->whereIn('item_id', $transfer->items->pluck('item_id'))->pluck('quantity_balance', 'item_id');

        return $this->nicePage('templates.storage_supplies.store_transfer.edit', 'storage-supplies.store-transfer.draft', [
            'transfer' => $transfer, 'balances' => $balances,
            'toSubdepartments' => Subdepartment::where('Subdepartment_ID', '!=', session('active_subdepartment_id'))->orderBy('Subdepartment_Name')->get(),
            'itemCategories' => Lookup::ofType('item_category')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, StoreTransfer $transfer): JsonResponse
    {
        abort_unless($transfer->status === 'draft', 403, 'Only draft transfers can be edited.');
        $data = $this->validatePayload($request, $transfer->id);
        $this->persist($data, $transfer);
        return response()->json(['success' => true]);
    }

    public function submit(StoreTransfer $transfer): JsonResponse
    {
        abort_unless($transfer->status === 'draft', 403, 'Only draft transfers can be submitted.');
        abort_unless($transfer->from_subdepartment_id === session('active_subdepartment_id'), 403);
        abort_unless($transfer->items()->exists(), 422, 'Add at least one item before submitting.');

        $transfer->update(['status' => 'pending_approval', 'submitted_by_user_id' => session('user_id'), 'submitted_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function cancel(Request $request, StoreTransfer $transfer): JsonResponse
    {
        abort_unless(in_array($transfer->status, ['draft', 'pending_approval'], true), 403, 'Only draft or pending-approval transfers can be cancelled.');
        abort_unless($transfer->from_subdepartment_id === session('active_subdepartment_id'), 403);

        $data = $request->validate(['reason' => 'required|string|max:255']);

        $transfer->update([
            'status' => 'cancelled', 'cancelled_by_user_id' => session('user_id'),
            'cancelled_at' => now(), 'cancel_reason' => $data['reason'],
        ]);

        return response()->json(['success' => true]);
    }

    public function approveList(): View
    {
        return $this->nicePage('templates.storage_supplies.store_transfer.approve_list', 'storage-supplies.store-transfer.approve', [
            'items' => StoreTransfer::with(['toSubdepartment', 'createdBy'])
                ->where('from_subdepartment_id', session('active_subdepartment_id'))
                ->where('status', 'pending_approval')->orderByDesc('id')->get(),
        ]);
    }

    // FEFO plan, identical technique to GRN Against Issue Note.
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

    public function approve(Request $request, StoreTransfer $transfer): JsonResponse
    {
        abort_unless($transfer->status === 'pending_approval', 403, 'Only transfers pending approval can be approved.');
        abort_unless($transfer->from_subdepartment_id === session('active_subdepartment_id'), 403);

        $credentials = $request->validate(['username' => 'required|string', 'password' => 'required|string']);
        $approver = User::where('email', $credentials['username'])->first();

        if (! $approver || ! Hash::check($credentials['password'], $approver->password)) {
            return response()->json(['message' => 'Invalid username or password.'], 422);
        }
        if (! $approver->hasApprovalPermission('store_transfer_approval')) {
            return response()->json(['message' => 'This user is not authorized to approve Store Transfers.'], 403);
        }

        $transfer->load('items.item');
        $fromSubId = $transfer->from_subdepartment_id;

        DB::transaction(function () use ($transfer, $approver, $fromSubId) {
            foreach ($transfer->items as $transferItem) {
                $result = $this->planFefo($transferItem->item_id, $fromSubId, $transferItem->quantity);

                if ($result['shortfall'] > 0) {
                    abort(422, "Insufficient batch stock for \"{$transferItem->item->product_name}\" — {$result['shortfall']} unit(s) short.");
                }

                foreach ($result['plan'] as $allocation) {
                    // Deduct immediately — goods are considered dispatched once approved.
                    $batch = $allocation['batch'];
                    $batch->decrement('quantity_remaining', $allocation['quantity']);

                    $balance = ItemStockBalance::where('item_id', $transferItem->item_id)->where('subdepartment_id', $fromSubId)->lockForUpdate()->first();
                    $newBalance = $balance->quantity_balance - $allocation['quantity'];
                    $balance->update(['quantity_balance' => $newBalance]);

                    StockLedger::create([
                        'item_id' => $transferItem->item_id, 'subdepartment_id' => $fromSubId,
                        'movement_type' => 'transfer_out', 'reference_type' => 'store_transfer', 'reference_id' => $transfer->id,
                        'quantity_in' => 0, 'quantity_out' => $allocation['quantity'], 'balance_after' => $newBalance,
                        'grn_batch_id' => null, 'created_by_user_id' => $approver->id, 'moved_at' => now(),
                    ]);

                    StoreTransferBatchAllocation::create([
                        'transfer_item_id' => $transferItem->id,
                        'stock_batch_id' => $batch->id,
                        'quantity_allocated' => $allocation['quantity'],
                    ]);
                }
            }

            $transfer->update(['status' => 'pending_receipt', 'approved_by_user_id' => $approver->id, 'approved_at' => now()]);
        });

        return response()->json(['success' => true]);
    }

    // Shared list — role per row determines which buttons the view shows.
    public function pendingReceiptList(): View
    {
        $activeSubId = session('active_subdepartment_id');

        $items = StoreTransfer::with(['fromSubdepartment', 'toSubdepartment', 'createdBy'])
            ->where('status', 'pending_receipt')
            ->where(function ($q) use ($activeSubId) {
                $q->where('from_subdepartment_id', $activeSubId)->orWhere('to_subdepartment_id', $activeSubId);
            })
            ->orderByDesc('id')->get()
            ->map(function ($transfer) use ($activeSubId) {
                $transfer->viewer_role = $transfer->to_subdepartment_id === $activeSubId ? 'receiver' : 'sender';
                return $transfer;
            });

        return $this->nicePage('templates.storage_supplies.store_transfer.pending_receipt_list', 'storage-supplies.store-transfer.pending-receipt', [
            'items' => $items,
        ]);
    }

    public function receive(Request $request, StoreTransfer $transfer): JsonResponse
    {
        abort_unless($transfer->status === 'pending_receipt', 403, 'Only transfers pending receipt can be received.');
        abort_unless($transfer->to_subdepartment_id === session('active_subdepartment_id'), 403, 'Only the receiving store can confirm receipt.');

        $transfer->load('items.allocations.stockBatch');
        $toSubId = $transfer->to_subdepartment_id;

        DB::transaction(function () use ($transfer, $toSubId) {
            foreach ($transfer->items as $transferItem) {
                foreach ($transferItem->allocations as $allocation) {
                    $sourceBatch = $allocation->stockBatch;

                    $newBatch = StockBatch::create([
                        'item_id' => $transferItem->item_id, 'subdepartment_id' => $toSubId,
                        'batch_number' => $sourceBatch->batch_number, 'manufacture_date' => $sourceBatch->manufacture_date,
                        'expiry_date' => $sourceBatch->expiry_date, 'buying_price' => $sourceBatch->buying_price,
                        'quantity_received' => $allocation->quantity_allocated, 'quantity_remaining' => $allocation->quantity_allocated,
                        'source_type' => 'store_transfer', 'source_id' => $transfer->id,
                        'received_date' => now()->toDateString(),
                    ]);

                    $balance = ItemStockBalance::firstOrCreate(
                        ['item_id' => $transferItem->item_id, 'subdepartment_id' => $toSubId],
                        ['quantity_balance' => 0]
                    );
                    $newBalance = $balance->quantity_balance + $allocation->quantity_allocated;
                    $balance->update(['quantity_balance' => $newBalance]);

                    StockLedger::create([
                        'item_id' => $transferItem->item_id, 'subdepartment_id' => $toSubId,
                        'movement_type' => 'transfer_in', 'reference_type' => 'store_transfer', 'reference_id' => $transfer->id,
                        'quantity_in' => $allocation->quantity_allocated, 'quantity_out' => 0, 'balance_after' => $newBalance,
                        'stock_batch_id' => $newBatch->id, 'created_by_user_id' => session('user_id'), 'moved_at' => now(),
                    ]);
                }
            }

            $transfer->update(['status' => 'completed', 'received_by_user_id' => session('user_id'), 'received_at' => now()]);
        });

        return response()->json(['success' => true]);
    }

    public function completedList(): View
    {
        $activeSubId = session('active_subdepartment_id');

        return $this->nicePage('templates.storage_supplies.store_transfer.completed_list', 'storage-supplies.store-transfer.completed', [
            'items' => StoreTransfer::with(['fromSubdepartment', 'toSubdepartment', 'createdBy', 'receivedBy'])
                ->where('status', 'completed')
                ->where(function ($q) use ($activeSubId) {
                    $q->where('from_subdepartment_id', $activeSubId)->orWhere('to_subdepartment_id', $activeSubId);
                })->orderByDesc('id')->get(),
        ]);
    }

    public function cancelledList(): View
    {
        return $this->nicePage('templates.storage_supplies.store_transfer.cancelled_list', 'storage-supplies.store-transfer.cancelled', [
            'items' => StoreTransfer::with(['toSubdepartment', 'createdBy', 'cancelledBy' => fn ($q) => $q]) //message : (remove the odd inline closure I accidentally left in cancelledList()'s eager-load — should just be 'cancelledBy' as a plain string once that relation exists.)
                ->where('from_subdepartment_id', session('active_subdepartment_id'))
                ->where('status', 'cancelled')->orderByDesc('id')->get(),
        ]);
    }

    public function preview(StoreTransfer $transfer): View
    {
        $transfer->load(['items.item.unitOfMeasure', 'fromSubdepartment.department.branch.company', 'toSubdepartment', 'createdBy', 'approvedBy', 'receivedBy']);
        $branch = $transfer->fromSubdepartment?->department?->branch;

        return view('templates.storage_supplies.store_transfer.preview', [
            'transfer' => $transfer, 'branch' => $branch, 'company' => $branch?->company,
        ]);
    }
}