<?php

namespace App\Http\Controllers\StorageSupplies;

use App\Http\Controllers\Controller;
use App\Models\GrnWithoutPo;
use App\Models\GrnWithoutPoBatch;
use App\Models\GrnWithoutPoEditHistory;
use App\Models\GrnWithoutPoItem;
use App\Models\ItemStockBalance;
use App\Models\Lookup;
use App\Models\StockBatch;
use App\Models\StockLedger;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class GrnWithoutPoController extends Controller
{
    public function create(): View
    {
        return $this->nicePage('templates.storage_supplies.grn_without_po.create', 'storage-supplies.grn-without-po.new', [
            'suppliers' => Supplier::orderBy('supplier_name')->get(),
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
            ->when($request->query('search'), fn ($q, $search) => $q->where('product_name', 'like', "%{$search}%"))
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
        $data = $request->validate([
            'supplier_id' => 'nullable|integer|exists:tbl_suppliers,id',
            'purchase_description' => 'nullable|string|max:255',
            'delivery_note_number' => 'required|string|max:255',
            'delivery_note_attachment' => 'nullable|file|max:5120',
            'invoice_number' => 'required|string|max:255',
            'invoice_attachment' => 'nullable|file|max:5120',
            'delivery_date' => 'required|date',
            'delivery_person' => 'nullable|string|max:255',
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
            'vat_charges' => 'nullable|numeric|min:0',
            'transport_charges' => 'nullable|numeric|min:0',
            'labor_charges' => 'nullable|numeric|min:0',
            'bank_charges' => 'nullable|numeric|min:0',
            'freight_charges' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
        ]);

        $deliveryNotePath = $request->hasFile('delivery_note_attachment')
            ? $request->file('delivery_note_attachment')->store('grn-attachments', 'public')
            : null;

        $invoicePath = $request->hasFile('invoice_attachment')
            ? $request->file('invoice_attachment')->store('grn-attachments', 'public')
            : null;

        $grn = DB::transaction(function () use ($data, $deliveryNotePath, $invoicePath) {
            $grn = GrnWithoutPo::create([
                'subdepartment_id' => session('active_subdepartment_id'),
                'supplier_id' => $data['supplier_id'] ?? null,
                'created_by_user_id' => session('user_id'),
                'purchase_description' => $data['purchase_description'] ?? null,
                'delivery_note_number' => $data['delivery_note_number'],
                'delivery_note_attachment' => $deliveryNotePath,
                'invoice_number' => $data['invoice_number'],
                'invoice_attachment' => $invoicePath,
                'delivery_date' => $data['delivery_date'],
                'delivery_person' => $data['delivery_person'] ?? null,
                'status' => 'pending_approval',
                'vat_charges' => $data['vat_charges'] ?? null,
                'transport_charges' => $data['transport_charges'] ?? null,
                'labor_charges' => $data['labor_charges'] ?? null,
                'bank_charges' => $data['bank_charges'] ?? null,
                'freight_charges' => $data['freight_charges'] ?? null,
                'other_charges' => $data['other_charges'] ?? null,
            ]);

            foreach ($data['items'] as $line) {
                $grnItem = GrnWithoutPoItem::create([
                    'grn_id' => $grn->id,
                    'item_id' => $line['item_id'],
                    'remarks' => $line['remarks'] ?? null,
                ]);

                foreach ($line['batches'] as $batch) {
                    GrnWithoutPoBatch::create([
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

        return response()->json(['success' => true, 'id' => $grn->id]);
    }

    public function pendingList(): View
    {
        return $this->nicePage('templates.storage_supplies.grn_without_po.pending_list', 'storage-supplies.grn-without-po.approve', [
            'items' => GrnWithoutPo::with(['subdepartment', 'supplier', 'createdBy'])
                ->where('subdepartment_id', session('active_subdepartment_id'))
                ->where('status', 'pending_approval')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function edit(GrnWithoutPo $grn): View
    {
        abort_unless($grn->status === 'pending_approval', 403, 'Only pending GRNs can be edited.');
        abort_unless($grn->subdepartment_id === session('active_subdepartment_id'), 403);

        $grn->load('items.item.unitOfMeasure', 'items.batches', 'supplier');

        return $this->nicePage('templates.storage_supplies.grn_without_po.edit', 'storage-supplies.grn-without-po.approve', [
            'grn' => $grn,
            'suppliers' => Supplier::orderBy('supplier_name')->get(),
            'itemCategories' => Lookup::ofType('item_category')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, GrnWithoutPo $grn): JsonResponse
    {
        abort_unless($grn->status === 'pending_approval', 403, 'Only pending GRNs can be edited.');

        $data = $request->validate([
            'supplier_id' => 'nullable|integer|exists:tbl_suppliers,id',
            'purchase_description' => 'nullable|string|max:255',
            'delivery_note_number' => 'required|string|max:255',
            'invoice_number' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'delivery_person' => 'nullable|string|max:255',
            'vat_charges' => 'nullable|numeric|min:0',
            'transport_charges' => 'nullable|numeric|min:0',
            'labor_charges' => 'nullable|numeric|min:0',
            'bank_charges' => 'nullable|numeric|min:0',
            'freight_charges' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
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

        $grn->load('items.item', 'items.batches');

        DB::transaction(function () use ($data, $grn) {
            // Snapshot exactly what's about to be overwritten, before touching anything.
            GrnWithoutPoEditHistory::create([
                'grn_id' => $grn->id,
                'edited_by_user_id' => session('user_id'),
                'previous_header' => $grn->only([
                    'supplier_id', 'purchase_description', 'delivery_note_number', 'invoice_number',
                    'delivery_date', 'delivery_person', 'vat_charges', 'transport_charges',
                    'labor_charges', 'bank_charges', 'freight_charges', 'other_charges',
                ]),
                'previous_items' => $grn->items->map(fn ($item) => [
                    'item_id' => $item->item_id,
                    'item_name' => $item->item->product_name ?? null,
                    'remarks' => $item->remarks,
                    'batches' => $item->batches->map(fn ($b) => $b->only([
                        'batch_number', 'units', 'items_per_unit', 'quantity', 'buying_price',
                        'manufacture_date', 'expiry_date', 'received_date',
                    ]))->all(),
                ])->all(),
                'edited_at' => now(),
            ]);

            $grn->update(collect($data)->except('items')->all());

            $grn->items()->delete();

            foreach ($data['items'] as $line) {
                $grnItem = GrnWithoutPoItem::create([
                    'grn_id' => $grn->id,
                    'item_id' => $line['item_id'],
                    'remarks' => $line['remarks'] ?? null,
                ]);

                foreach ($line['batches'] as $batch) {
                    GrnWithoutPoBatch::create([
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
        });

        $grn->load('items.batches');

        return response()->json([
            'success' => true,
            'saved_item_count' => $grn->items->count(),
            'saved_batch_count' => $grn->items->sum(fn ($item) => $item->batches->count()),
        ]);
    }

    public function approve(Request $request, GrnWithoutPo $grn): JsonResponse
    {
        abort_unless($grn->status === 'pending_approval', 403, 'Only pending GRNs can be approved.');
        abort_unless($grn->subdepartment_id === session('active_subdepartment_id'), 403);

        $credentials = $request->validate(['username' => 'required|string', 'password' => 'required|string']);
        $approver = User::where('email', $credentials['username'])->first();

        if (! $approver || ! Hash::check($credentials['password'], $approver->password)) {
            return response()->json(['message' => 'Invalid username or password.'], 422);
        }

        if (! $approver->hasApprovalPermission('grn_without_order_approval')) {
            return response()->json(['message' => 'This user is not authorized to approve GRNs Without Purchase Order.'], 403);
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
                        'movement_type' => 'grn_receipt',
                        'reference_type' => 'grn_without_po',
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
                        'source_type' => 'grn_without_po',
                        'source_id' => $grn->id,
                        'received_date' => $batch->received_date,
                    ]);
                }
            }

            $grn->update([
                'status' => 'approved',
                'approved_by_user_id' => $approver->id,
                'approved_at' => now(),
            ]);
        });

        return response()->json(['success' => true]);
    }

    public function previousList(): View
    {
        return $this->nicePage('templates.storage_supplies.grn_without_po.previous_list', 'storage-supplies.grn-without-po.previous', [
            'items' => GrnWithoutPo::with(['subdepartment', 'createdBy', 'items.batches'])
                ->where('subdepartment_id', session('active_subdepartment_id'))
                ->where('status', 'approved')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function preview(GrnWithoutPo $grn): View
    {
        $grn->load(['items.item.unitOfMeasure', 'items.batches', 'subdepartment.department.branch.company', 'supplier', 'createdBy', 'approvedBy']);

        $branch = $grn->subdepartment?->department?->branch;

        return view('templates.storage_supplies.grn_without_po.preview', [
            'grn' => $grn,
            'branch' => $branch,
            'company' => $branch?->company,
        ]);
    }
}