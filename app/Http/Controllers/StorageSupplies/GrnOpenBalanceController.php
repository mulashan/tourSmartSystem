<?php

namespace App\Http\Controllers\StorageSupplies;

use App\Http\Controllers\Controller;
use App\Models\GrnOpenBalance;
use App\Models\GrnOpenBalanceBatch;
use App\Models\GrnOpenBalanceItem;
use App\Models\ItemStockBalance;
use App\Models\Lookup;
use App\Models\StockBatch;
use App\Models\StockLedger;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class GrnOpenBalanceController extends Controller
{
    public function newList(): View
    {
        return $this->nicePage('templates.storage_supplies.grn_open_balance.new_list', 'storage-supplies.grn-open-balance.new', [
            'items' => GrnOpenBalance::with('createdBy')
                ->where('subdepartment_id', session('active_subdepartment_id'))
                ->where('status', 'draft')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return $this->nicePage('templates.storage_supplies.grn_open_balance.create', 'storage-supplies.grn-open-balance.new', [
            'itemCategories' => Lookup::ofType('item_category')->orderBy('name')->get(),
        ]);
    }

    public function itemsPicker(Request $request): JsonResponse
    {
        $subdepartmentId = session('active_subdepartment_id');

        $items = \App\Models\Item::query()
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

        return response()->json($items->map(fn ($item) => [
            'id' => $item->id,
            'name' => $item->product_name,
            'uom' => $item->unitOfMeasure->name ?? '',
            'balance' => $balances->get($item->id, 0),
        ]));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatePayload($request);

        $grn = $this->persist($data, null, $data['submit'] ? 'pending_approval' : 'draft');

        return response()->json(['success' => true, 'id' => $grn->id]);
    }

    public function edit(GrnOpenBalance $grn): View
    {
        abort_unless($grn->status === 'draft', 403, 'Only draft entries can be edited.');
        abort_unless($grn->subdepartment_id === session('active_subdepartment_id'), 403);

        $grn->load('items.item.unitOfMeasure', 'items.batches');

        return $this->nicePage('templates.storage_supplies.grn_open_balance.edit', 'storage-supplies.grn-open-balance.new', [
            'grn' => $grn,
            'itemCategories' => Lookup::ofType('item_category')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, GrnOpenBalance $grn): JsonResponse
    {
        abort_unless($grn->status === 'draft', 403, 'Only draft entries can be edited.');

        $data = $this->validatePayload($request);

        $this->persist($data, $grn, $data['submit'] ? 'pending_approval' : 'draft');

        return response()->json(['success' => true]);
    }

    private function validatePayload(Request $request): array
    {
        $validated = $request->validate([
            'creation_date' => 'required|date',
            'description' => 'nullable|string|max:255',
            'submit' => 'nullable|boolean',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:tbl_items,id',
            'items.*.remarks' => 'nullable|string|max:255',
            'items.*.batches' => 'required|array|min:1',
            'items.*.batches.*.batch_number' => 'required|string|max:100',
            'items.*.batches.*.units' => 'required|integer|min:1',
            'items.*.batches.*.items_per_unit' => 'required|integer|min:1',
            'items.*.batches.*.buying_price' => 'required|numeric|min:0',
            'items.*.batches.*.manufacture_date' => 'required|date|before_or_equal:today',
            'items.*.batches.*.expiry_date' => 'required|date|after_or_equal:today',
            'items.*.batches.*.received_date' => 'required|date',
        ]);

        $validated['submit'] = (bool) ($validated['submit'] ?? false);

        return $validated;
    }

    private function persist(array $data, ?GrnOpenBalance $grn, string $status): GrnOpenBalance
    {
        return DB::transaction(function () use ($data, $grn, $status) {
            if ($grn) {
                $grn->update([
                    'creation_date' => $data['creation_date'],
                    'description' => $data['description'] ?? null,
                    'status' => $status,
                    'submitted_by_user_id' => $status === 'pending_approval' ? session('user_id') : $grn->submitted_by_user_id,
                    'submitted_at' => $status === 'pending_approval' ? now() : $grn->submitted_at,
                ]);
                $grn->items()->delete();
            } else {
                $grn = GrnOpenBalance::create([
                    'subdepartment_id' => session('active_subdepartment_id'),
                    'created_by_user_id' => session('user_id'),
                    'creation_date' => $data['creation_date'],
                    'description' => $data['description'] ?? null,
                    'status' => $status,
                    'submitted_by_user_id' => $status === 'pending_approval' ? session('user_id') : null,
                    'submitted_at' => $status === 'pending_approval' ? now() : null,
                ]);
            }

            foreach ($data['items'] as $line) {
                $grnItem = GrnOpenBalanceItem::create([
                    'grn_id' => $grn->id,
                    'item_id' => $line['item_id'],
                    'remarks' => $line['remarks'] ?? null,
                ]);

                foreach ($line['batches'] as $batch) {
                    GrnOpenBalanceBatch::create([
                        'grn_item_id' => $grnItem->id,
                        'batch_number' => $batch['batch_number'],
                        'units' => $batch['units'],
                        'items_per_unit' => $batch['items_per_unit'],
                        'quantity' => $batch['units'] * $batch['items_per_unit'],
                        'buying_price' => $batch['buying_price'],
                        'manufacture_date' => $batch['manufacture_date'],
                        'expiry_date' => $batch['expiry_date'],
                        'received_date' => $batch['received_date'],
                    ]);
                }
            }

            return $grn;
        });
    }

    public function submit(GrnOpenBalance $grn): JsonResponse
    {
        abort_unless($grn->status === 'draft', 403, 'Only draft entries can be submitted.');
        abort_unless($grn->items()->exists(), 422, 'Add at least one item before submitting.');

        $grn->update([
            'status' => 'pending_approval',
            'submitted_by_user_id' => session('user_id'),
            'submitted_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function approveList(): View
    {
        return $this->nicePage('templates.storage_supplies.grn_open_balance.approve_list', 'storage-supplies.grn-open-balance.approve', [
            'items' => GrnOpenBalance::with(['subdepartment', 'createdBy'])
                ->where('subdepartment_id', session('active_subdepartment_id'))
                ->where('status', 'pending_approval')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function approve(Request $request, GrnOpenBalance $grn): JsonResponse
    {
        abort_unless($grn->status === 'pending_approval', 403, 'Only entries pending approval can be approved.');
        abort_unless($grn->subdepartment_id === session('active_subdepartment_id'), 403);

        $credentials = $request->validate(['username' => 'required|string', 'password' => 'required|string']);
        $approver = User::where('email', $credentials['username'])->first();

        if (! $approver || ! Hash::check($credentials['password'], $approver->password)) {
            return response()->json(['message' => 'Invalid username or password.'], 422);
        }

        if (! $approver->hasApprovalPermission('grn_open_balance_approval')) {
            return response()->json(['message' => 'This user is not authorized to approve Open Balance / Physical Count entries.'], 403);
        }

        $grn->load('items.batches');

        DB::transaction(function () use ($grn, $approver) {
            foreach ($grn->items as $grnItem) {
                foreach ($grnItem->batches as $batch) {
                    $balance = ItemStockBalance::firstOrCreate(
                        ['item_id' => $grnItem->item_id, 'subdepartment_id' => $grn->subdepartment_id],
                        ['quantity_balance' => 0]
                    );

                    $newBalance = $balance->quantity_balance + $batch->quantity;
                    $balance->update(['quantity_balance' => $newBalance]);

                    StockLedger::create([
                        'item_id' => $grnItem->item_id,
                        'subdepartment_id' => $grn->subdepartment_id,
                        'movement_type' => 'opening_balance',
                        'reference_type' => 'grn_open_balance',
                        'reference_id' => $grn->id,
                        'quantity_in' => $batch->quantity,
                        'quantity_out' => 0,
                        'balance_after' => $newBalance,
                        'grn_batch_id' => null,
                        'created_by_user_id' => $approver->id,
                        'moved_at' => now(),
                    ]);
                    StockBatch::create([
                        'item_id' => $grnItem->item_id,
                        'subdepartment_id' => $grn->subdepartment_id,
                        'batch_number' => $batch->batch_number,
                        'manufacture_date' => $batch->manufacture_date,
                        'expiry_date' => $batch->expiry_date,
                        'buying_price' => $batch->buying_price,
                        'quantity_received' => $batch->quantity,
                        'quantity_remaining' => $batch->quantity,
                        'source_type' => 'grn_open_balance',
                        'source_id' => $grn->id,
                        'received_date' => $batch->received_date,
                    ]);
                }
            }

            $grn->update(['status' => 'approved', 'approved_by_user_id' => $approver->id, 'approved_at' => now()]);
        });

        return response()->json(['success' => true]);
    }

    public function previousList(): View
    {
        return $this->nicePage('templates.storage_supplies.grn_open_balance.previous_list', 'storage-supplies.grn-open-balance.previous', [
            'items' => GrnOpenBalance::with(['subdepartment', 'createdBy', 'items.batches'])
                ->where('subdepartment_id', session('active_subdepartment_id'))
                ->where('status', 'approved')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function preview(GrnOpenBalance $grn): View
    {
        $grn->load(['items.item.unitOfMeasure', 'items.batches', 'subdepartment.department.branch.company', 'createdBy', 'approvedBy']);

        $branch = $grn->subdepartment?->department?->branch;

        return view('templates.storage_supplies.grn_open_balance.preview', [
            'grn' => $grn,
            'branch' => $branch,
            'company' => $branch?->company,
        ]);
    }
}