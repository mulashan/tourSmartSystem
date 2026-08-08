<?php

namespace App\Http\Controllers\StorageSupplies;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemStockBalance;
use App\Models\Lookup;
use App\Models\Return_;
use App\Models\ReturnBatchAllocation;
use App\Models\ReturnItem;
use App\Models\StockBatch;
use App\Models\StockLedger;
use App\Models\Subdepartment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ReturnController extends Controller
{
    public function draftList(): View
    {
        return $this->nicePage('templates.storage_supplies.return.draft_list', 'storage-supplies.return.new', [
            'items' => Return_::with(['toSubdepartment', 'postedBy'])
                ->where('from_subdepartment_id', session('active_subdepartment_id'))
                ->where('status', 'draft')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return $this->nicePage('templates.storage_supplies.return.create', 'storage-supplies.return.new', [
            'nextDocumentNumberPreview' => (int) (Return_::max('id') ?? 0) + 1,
            'toSubdepartments' => Subdepartment::where('Subdepartment_ID', '!=', session('active_subdepartment_id'))
                ->orderBy('Subdepartment_Name')
                ->get(),
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

    private function validatePayload(Request $request, ?int $lockedToSubdepartmentId = null): array
    {
        $rules = [
            'return_date' => 'required|date',
            'description' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:tbl_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];

        if (! $lockedToSubdepartmentId) {
            $rules['to_subdepartment_id'] = 'required|integer|exists:tbl_subdepartment,Subdepartment_ID';
        }

        $validated = $request->validate($rules);
        $validated['to_subdepartment_id'] = $lockedToSubdepartmentId ?? $validated['to_subdepartment_id'];

        if ($validated['to_subdepartment_id'] == session('active_subdepartment_id')) {
            abort(422, 'Cannot return to the same store.');
        }

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

    private function persist(array $data, ?Return_ $return): Return_
    {
        return DB::transaction(function () use ($data, $return) {
            if ($return) {
                $return->update(['description' => $data['description'], 'return_date' => $data['return_date']]);
                $return->items()->delete();
            } else {
                $return = Return_::create([
                    'return_date' => $data['return_date'],
                    'from_subdepartment_id' => session('active_subdepartment_id'),
                    'to_subdepartment_id' => $data['to_subdepartment_id'],
                    'posted_by_user_id' => session('user_id'),
                    'description' => $data['description'],
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

    public function edit(Return_ $return): View
    {
        abort_unless($return->status === 'draft', 403, 'Only draft returns can be edited.');
        abort_unless($return->from_subdepartment_id === session('active_subdepartment_id'), 403);

        $return->load('items.item.unitOfMeasure', 'toSubdepartment');

        $balances = ItemStockBalance::where('subdepartment_id', session('active_subdepartment_id'))
            ->whereIn('item_id', $return->items->pluck('item_id'))
            ->pluck('quantity_balance', 'item_id');

        return $this->nicePage('templates.storage_supplies.return.edit', 'storage-supplies.return.new', [
            'return' => $return,
            'balances' => $balances,
            'itemCategories' => Lookup::ofType('item_category')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Return_ $return): JsonResponse
    {
        abort_unless($return->status === 'draft', 403, 'Only draft returns can be edited.');

        $data = $this->validatePayload($request, $return->to_subdepartment_id);
        $this->persist($data, $return);

        return response()->json(['success' => true]);
    }

    public function submit(Return_ $return): JsonResponse
    {
        abort_unless($return->status === 'draft', 403, 'Only draft returns can be submitted.');
        abort_unless($return->from_subdepartment_id === session('active_subdepartment_id'), 403);
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
        return $this->nicePage('templates.storage_supplies.return.approve_list', 'storage-supplies.return.approve', [
            'items' => Return_::with(['toSubdepartment', 'postedBy'])
                ->where('from_subdepartment_id', session('active_subdepartment_id'))
                ->where('status', 'pending_approval')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    // FEFO plan — oldest expiry first, from the returning store's own stock.
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

    public function approve(Request $request, Return_ $return): JsonResponse
    {
        abort_unless($return->status === 'pending_approval', 403, 'Only returns pending approval can be approved.');
        abort_unless($return->from_subdepartment_id === session('active_subdepartment_id'), 403);

        $credentials = $request->validate(['username' => 'required|string', 'password' => 'required|string']);
        $approver = User::where('email', $credentials['username'])->first();

        if (! $approver || ! Hash::check($credentials['password'], $approver->password)) {
            return response()->json(['message' => 'Invalid username or password.'], 422);
        }

        if (! $approver->hasApprovalPermission('return_inward_approval')) {
            return response()->json(['message' => 'This user is not authorized to approve Returns.'], 403);
        }

        $return->load('items.item');
        $fromSubId = $return->from_subdepartment_id;

        DB::transaction(function () use ($return, $approver, $fromSubId) {
            foreach ($return->items as $returnItem) {
                $result = $this->planFefo($returnItem->item_id, $fromSubId, $returnItem->quantity);

                if ($result['shortfall'] > 0) {
                    abort(422, "Insufficient batch stock for \"{$returnItem->item->product_name}\" — {$result['shortfall']} unit(s) short.");
                }

                foreach ($result['plan'] as $allocation) {
                    $batch = $allocation['batch'];
                    $batch->decrement('quantity_remaining', $allocation['quantity']);

                    $balance = ItemStockBalance::where('item_id', $returnItem->item_id)
                        ->where('subdepartment_id', $fromSubId)
                        ->lockForUpdate()
                        ->first();
                    $newBalance = $balance->quantity_balance - $allocation['quantity'];
                    $balance->update(['quantity_balance' => $newBalance]);

                    StockLedger::create([
                        'item_id' => $returnItem->item_id,
                        'subdepartment_id' => $fromSubId,
                        'movement_type' => 'return_out',
                        'reference_type' => 'return',
                        'reference_id' => $return->id,
                        'quantity_in' => 0,
                        'quantity_out' => $allocation['quantity'],
                        'balance_after' => $newBalance,
                        'grn_batch_id' => null,
                        'created_by_user_id' => $approver->id,
                        'moved_at' => now(),
                    ]);

                    ReturnBatchAllocation::create([
                        'return_item_id' => $returnItem->id,
                        'stock_batch_id' => $batch->id,
                        'quantity_allocated' => $allocation['quantity'],
                    ]);
                }
            }

            $return->update(['status' => 'pending_receipt', 'approved_by_user_id' => $approver->id, 'approved_at' => now()]);
        });

        return response()->json(['success' => true]);
    }

    // Shared list — role per row (based on active subdepartment) determines button visibility.
    public function returnList(): View
    {
        $activeSubId = session('active_subdepartment_id');

        $items = Return_::with(['fromSubdepartment', 'toSubdepartment', 'postedBy'])
            ->where('status', 'pending_receipt')
            ->where(function ($q) use ($activeSubId) {
                $q->where('from_subdepartment_id', $activeSubId)->orWhere('to_subdepartment_id', $activeSubId);
            })
            ->orderByDesc('id')
            ->get()
            ->map(function ($return) use ($activeSubId) {
                $return->viewer_role = $return->to_subdepartment_id === $activeSubId ? 'receiver' : 'sender';
                return $return;
            });

        return $this->nicePage('templates.storage_supplies.return.return_list', 'storage-supplies.return.return-list', [
            'items' => $items,
        ]);
    }

    public function receive(Return_ $return): JsonResponse
    {
        abort_unless($return->status === 'pending_receipt', 403, 'Only returns pending receipt can be received.');
        abort_unless($return->to_subdepartment_id === session('active_subdepartment_id'), 403, 'Only the receiving store can confirm receipt.');

        $return->load('items.allocations.stockBatch');
        $toSubId = $return->to_subdepartment_id;

        DB::transaction(function () use ($return, $toSubId) {
            foreach ($return->items as $returnItem) {
                foreach ($returnItem->allocations as $allocation) {
                    $sourceBatch = $allocation->stockBatch;

                    $newBatch = StockBatch::create([
                        'item_id' => $returnItem->item_id,
                        'subdepartment_id' => $toSubId,
                        'batch_number' => $sourceBatch->batch_number,
                        'manufacture_date' => $sourceBatch->manufacture_date,
                        'expiry_date' => $sourceBatch->expiry_date,
                        'buying_price' => $sourceBatch->buying_price,
                        'quantity_received' => $allocation->quantity_allocated,
                        'quantity_remaining' => $allocation->quantity_allocated,
                        'source_type' => 'return',
                        'source_id' => $return->id,
                        'received_date' => now()->toDateString(),
                    ]);

                    $balance = ItemStockBalance::firstOrCreate(
                        ['item_id' => $returnItem->item_id, 'subdepartment_id' => $toSubId],
                        ['quantity_balance' => 0]
                    );
                    $newBalance = $balance->quantity_balance + $allocation->quantity_allocated;
                    $balance->update(['quantity_balance' => $newBalance]);

                    StockLedger::create([
                        'item_id' => $returnItem->item_id,
                        'subdepartment_id' => $toSubId,
                        'movement_type' => 'return_in',
                        'reference_type' => 'return',
                        'reference_id' => $return->id,
                        'quantity_in' => $allocation->quantity_allocated,
                        'quantity_out' => 0,
                        'balance_after' => $newBalance,
                        'stock_batch_id' => $newBatch->id,
                        'created_by_user_id' => session('user_id'),
                        'moved_at' => now(),
                    ]);
                }
            }

            $return->update(['status' => 'completed', 'received_by_user_id' => session('user_id'), 'received_at' => now()]);
        });

        return response()->json(['success' => true]);
    }

    public function previousList(): View
    {
        $activeSubId = session('active_subdepartment_id');

        return $this->nicePage('templates.storage_supplies.return.previous_list', 'storage-supplies.return.previous', [
            'items' => Return_::with(['fromSubdepartment', 'toSubdepartment', 'postedBy', 'receivedBy'])
                ->where('status', 'completed')
                ->where(function ($q) use ($activeSubId) {
                    $q->where('from_subdepartment_id', $activeSubId)->orWhere('to_subdepartment_id', $activeSubId);
                })
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function preview(Return_ $return): View
    {
        $return->load(['items.item.unitOfMeasure', 'fromSubdepartment.department.branch.company', 'toSubdepartment', 'postedBy', 'approvedBy', 'receivedBy']);

        $branch = $return->fromSubdepartment?->department?->branch;

        return view('templates.storage_supplies.return.preview', [
            'return' => $return,
            'branch' => $branch,
            'company' => $branch?->company,
        ]);
    }
}