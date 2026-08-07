<?php

namespace App\Http\Controllers\StorageSupplies;

use App\Http\Controllers\Controller;
use App\Models\GrnBatch;
use App\Models\GrnItem;
use App\Models\GrnPurchaseOrder;
use App\Models\ItemStockBalance;
use App\Models\LocalPurchaseOrder;
use App\Models\StockBatch;
use App\Models\StockLedger;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GrnController extends Controller
{
    public function newGrnList(): View
    {
        return $this->nicePage('templates.storage_supplies.grn.new_list', 'storage-supplies.grn.new', [
            'lpos' => LocalPurchaseOrder::with(['storeRequisition.subdepartment', 'supplier', 'createdBy'])
                ->where('status', 'approved')
                ->whereDoesntHave('grn')
                ->whereHas('storeRequisition', fn ($q) => $q->where('subdepartment_id', session('active_subdepartment_id')))
                ->orderByDesc('local_purchase_order_id')
                ->get(),
        ]);
    }

    public function create(LocalPurchaseOrder $localPurchaseOrder): View
    {
        abort_if($localPurchaseOrder->grn, 409, 'A GRN already exists for this Purchase Order.');
        abort_unless($localPurchaseOrder->storeRequisition?->subdepartment_id === session('active_subdepartment_id'), 403);

        $localPurchaseOrder->load(['items.item.unitOfMeasure', 'storeRequisition.subdepartment', 'supplier', 'createdBy']);

        return $this->nicePage('templates.storage_supplies.grn.create', 'storage-supplies.grn.new', [
            'lpo' => $localPurchaseOrder,
        ]);
    }

    public function store(Request $request, LocalPurchaseOrder $localPurchaseOrder): JsonResponse
    {
        abort_if($localPurchaseOrder->grn, 409, 'A GRN already exists for this Purchase Order.');

        $data = $request->validate([
            'delivery_note_number' => 'required|string|max:255',
            'delivery_note_attachment' => 'nullable|file|max:5120',
            'invoice_number' => 'required|string|max:255',
            'invoice_attachment' => 'nullable|file|max:5120',
            'delivery_date' => 'required|date',
            'delivery_person' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.lpo_item_id' => 'required|integer|exists:tbl_local_purchase_order_items,lpo_item_id',
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

        // Server-side re-verification: every LPO item must be present and its batch total must match exactly.
        foreach ($data['items'] as $line) {
            $lpoItem = $localPurchaseOrder->items()->findOrFail($line['lpo_item_id']);
            $batchTotal = collect($line['batches'])->sum(fn ($b) => $b['units'] * $b['items_per_unit']);

            if ($batchTotal !== (int) $lpoItem->Quantity_Required) {
                return response()->json([
                    'message' => "Batch quantity for \"{$lpoItem->item->product_name}\" ({$batchTotal}) does not match the purchased quantity ({$lpoItem->Quantity_Required}).",
                ], 422);
            }
        }

        $deliveryNotePath = $request->hasFile('delivery_note_attachment')
            ? $request->file('delivery_note_attachment')->store('grn-attachments', 'public')
            : null;

        $invoicePath = $request->hasFile('invoice_attachment')
            ? $request->file('invoice_attachment')->store('grn-attachments', 'public')
            : null;

        $grn = DB::transaction(function () use ($data, $localPurchaseOrder, $deliveryNotePath, $invoicePath) {
            $grn = GrnPurchaseOrder::create([
                'local_purchase_order_id' => $localPurchaseOrder->local_purchase_order_id,
                'supplier_id' => $localPurchaseOrder->supplier_id,
                'created_by_user_id' => session('user_id'),
                'Sub_Department_ID' => session('active_subdepartment_id'),
                'Purchase_Description' => $localPurchaseOrder->requisition_description,
                'Delivery_Note_Number' => $data['delivery_note_number'],
                'Delivery_Note_Attachment' => $deliveryNotePath,
                'Invoice_Number' => $data['invoice_number'],
                'Invoice_Attachment' => $invoicePath,
                'Delivery_Date' => $data['delivery_date'],
                'Delivery_Person' => $data['delivery_person'] ?? null,
                'status' => 'draft',
            ]);

            foreach ($data['items'] as $line) {
                $lpoItem = $localPurchaseOrder->items()->findOrFail($line['lpo_item_id']);

                $grnItem = GrnItem::create([
                    'grn_id' => $grn->Grn_Purchase_Order_ID,
                    'lpo_item_id' => $lpoItem->lpo_item_id,
                    'item_id' => $lpoItem->Item_ID,
                    'remarks' => $line['remarks'] ?? null,
                ]);

                foreach ($line['batches'] as $batch) {
                    GrnBatch::create([
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

        return response()->json(['success' => true, 'id' => $grn->Grn_Purchase_Order_ID]);
    }

    public function submitForInspection(GrnPurchaseOrder $grn): JsonResponse
    {
        abort_unless($grn->status === 'draft', 403, 'Only draft GRNs can be submitted.');

        $grn->load('items.batches', 'localPurchaseOrder.items');

        foreach ($grn->localPurchaseOrder->items as $lpoItem) {
            $grnItem = $grn->items->firstWhere('lpo_item_id', $lpoItem->lpo_item_id);

            if (! $grnItem || $grnItem->batches->isEmpty()) {
                return response()->json(['message' => "\"{$lpoItem->item->product_name}\" has no batch specified."], 422);
            }

            $batchTotal = $grnItem->batches->sum(fn ($b) => $b->units * $b->items_per_unit);

            if ($batchTotal !== (int) $lpoItem->Quantity_Required) {
                return response()->json(['message' => "Batch total for \"{$lpoItem->item->product_name}\" does not match purchased quantity."], 422);
            }
        }

        $grn->update([
            'status' => 'pending_approval',
            'submitted_by_user_id' => session('user_id'),
            'submitted_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function approveGrnList(): View
    {
        return $this->nicePage('templates.storage_supplies.grn.approve_list', 'storage-supplies.grn.approve', [
            'items' => GrnPurchaseOrder::with(['localPurchaseOrder', 'subdepartment', 'supplier', 'createdBy'])
                ->where('Sub_Department_ID', session('active_subdepartment_id'))
                ->where('status', 'pending_approval')
                ->orderByDesc('Grn_Purchase_Order_ID')
                ->get(),
        ]);
    }

    public function approve(Request $request, GrnPurchaseOrder $grn): JsonResponse
    {
        abort_unless($grn->status === 'pending_approval', 403, 'Only GRNs pending approval can be approved.');
        abort_unless($grn->Sub_Department_ID === session('active_subdepartment_id'), 403);

        $credentials = $request->validate(['username' => 'required|string', 'password' => 'required|string']);
        $approver = User::where('email', $credentials['username'])->first();

        if (! $approver || ! Hash::check($credentials['password'], $approver->password)) {
            return response()->json(['message' => 'Invalid username or password.'], 422);
        }

        if (! $approver->hasApprovalPermission('grn_against_order_approval')) {
            return response()->json(['message' => 'This user is not authorized to approve GRNs.'], 403);
        }

        $grn->load('items.batches');

        DB::transaction(function () use ($grn, $approver) {
            foreach ($grn->items as $grnItem) {
                foreach ($grnItem->batches as $batch) {
                    $balance = ItemStockBalance::firstOrCreate(
                        ['item_id' => $grnItem->item_id, 'subdepartment_id' => $grn->Sub_Department_ID],
                        ['quantity_balance' => 0]
                    );

                    $newBalance = $balance->quantity_balance + $batch->quantity;
                    $balance->update(['quantity_balance' => $newBalance]);

                    StockLedger::create([
                        'item_id' => $grnItem->item_id,
                        'subdepartment_id' => $grn->Sub_Department_ID,
                        'movement_type' => 'grn_receipt',
                        'reference_type' => 'grn',
                        'reference_id' => $grn->Grn_Purchase_Order_ID,
                        'quantity_in' => $batch->quantity,
                        'quantity_out' => 0,
                        'balance_after' => $newBalance,
                        'grn_batch_id' => $batch->id,
                        'created_by_user_id' => $approver->id,
                        'moved_at' => now(),
                    ]);

                    StockBatch::create([
                        'item_id' => $grnItem->item_id,
                        'subdepartment_id' => $grn->Sub_Department_ID,
                        'batch_number' => $batch->batch_number,
                        'manufacture_date' => $batch->manufacture_date,
                        'expiry_date' => $batch->expiry_date,
                        'buying_price' => $batch->buying_price,
                        'quantity_received' => $batch->quantity,
                        'quantity_remaining' => $batch->quantity,
                        'source_type' => 'grn_against_po',
                        'source_id' => $grn->Grn_Purchase_Order_ID,
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

    public function previousGrnList(): View
    {
        return $this->nicePage('templates.storage_supplies.grn.previous_list', 'storage-supplies.grn.previous', [
            'items' => GrnPurchaseOrder::with(['localPurchaseOrder', 'subdepartment', 'createdBy', 'items.batches'])
                ->where('Sub_Department_ID', session('active_subdepartment_id'))
                ->where('status', 'approved')
                ->orderByDesc('Grn_Purchase_Order_ID')
                ->get(),
        ]);
    }

    public function preview(GrnPurchaseOrder $grn): View
    {
        $grn->load(['items.item.unitOfMeasure', 'items.batches', 'localPurchaseOrder.storeRequisition.subdepartment.department.branch.company', 'supplier', 'createdBy', 'approvedBy']);

        $branch = $grn->localPurchaseOrder?->storeRequisition?->subdepartment?->department?->branch;

        return view('templates.storage_supplies.grn.preview', [
            'grn' => $grn,
            'branch' => $branch,
            'company' => $branch?->company,
        ]);
    }
}