<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\LocalPurchaseOrder;
use App\Models\StoreRequisition;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Models\User; 
use Illuminate\Support\Facades\Hash;

class ProcurementController extends Controller
{
    public function storeRequisitions(): View|RedirectResponse
    {
        return $this->nicePage('templates.procurement.store_requisitions.list', 'procurement.store-requisitions', [
            'items' => StoreRequisition::with(['subdepartment', 'preparedBy'])
                ->where('status', 'approved')
                ->where('procurement_status', 'pending')
                ->whereDoesntHave('localPurchaseOrder')
                ->orderByDesc('id')
                ->get(),
            'suppliers' => Supplier::orderBy('supplier_name')->get(),
        ]);
    }

    public function createPurchaseOrder(StoreRequisition $storeRequisition): View|RedirectResponse
    {
        abort_if($storeRequisition->localPurchaseOrder, 409, 'A Purchase Order already exists for this Store Requisition.');

        $storeRequisition->load('items.item.unitOfMeasure');

        return $this->nicePage('templates.procurement.store_requisitions.create_purchase_order', 'procurement.store-requisitions', [
            'storeRequisition' => $storeRequisition,
            'suppliers' => Supplier::orderBy('supplier_name')->get(),
        ]);
    }

    public function storePurchaseOrder(Request $request, StoreRequisition $storeRequisition): JsonResponse
    {
        abort_if($storeRequisition->localPurchaseOrder, 409, 'A Purchase Order already exists for this Store Requisition.');

        $data = $request->validate([
            'supplier_id' => 'nullable|integer|exists:tbl_suppliers,id',
            'currency_type' => 'required|string|max:10',
            'requisition_description' => 'required|string|max:255',
            'vat_charges' => 'nullable|string|max:255',
            'transport_charges' => 'nullable|string|max:255',
            'labor_charges' => 'nullable|string|max:255',
            'bank_charges' => 'nullable|string|max:255',
            'freight_charges' => 'nullable|string|max:255',
            'other_charges' => 'nullable|string|max:255',
            'items' => 'nullable|array',
            'items.*.requisition_item_id' => 'required|integer|exists:tbl_store_requisition_items,id',
            'items.*.units' => 'required|integer|min:1',
            'items.*.items_per_unit' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0.01',
            'rejected_item_ids' => 'nullable|array',
            'rejected_item_ids.*' => 'integer|exists:tbl_store_requisition_items,id',
        ]);

        //we need to update store requisition procurement status to ordered storeRequisition
        if (empty($data['items'])) {
            return response()->json(['message' => 'Select at least one item to include in the Purchase Order.'], 422);
        }

        $lpo = DB::transaction(function () use ($data, $storeRequisition) {
            $lpo = LocalPurchaseOrder::create([
                'store_requisition_id' => $storeRequisition->id,
                'supplier_id' => $data['supplier_id'] ?? null,
                'currency_type' => $data['currency_type'],
                'requisition_description' => $data['requisition_description'],
                'created_by_user_id' => session('user_id'),
                'procurement_subdepartment_id' => session('active_subdepartment_id'),
                'order_date' => now()->toDateString(),
                'status' => 'draft',
                'vat_charges' => $data['vat_charges'] ?? null,
                'labor_charges' => $data['labor_charges'] ?? null,
                'transport_charges' => $data['transport_charges'] ?? null,
                'freight_charges' => $data['freight_charges'] ?? null,
                'bank_charges' => $data['bank_charges'] ?? null,
                'other_charges' => $data['other_charges'] ?? null,
            ]);

            foreach ($data['items'] as $line) {
                $requisitionItem = $storeRequisition->items()->findOrFail($line['requisition_item_id']);

                $lpo->items()->create([
                    'Item_ID' => $requisitionItem->item_id,
                    'Containers_Required' => $line['units'],
                    'Items_Per_Container_Required' => $line['items_per_unit'],
                    'Quantity_Required' => $line['units'] * $line['items_per_unit'],
                    'Price' => $line['price'],
                    'Remarks' => null,
                    'Remain_Balance' => $line['units'] * $line['items_per_unit'],
                ]);

                $requisitionItem->update(['procurement_status' => 'ordered']);
            }

            foreach ($data['rejected_item_ids'] ?? [] as $itemId) {
                $storeRequisition->items()->where('id', $itemId)->update(['procurement_status' => 'rejected']);
            }

            $storeRequisition->update([ 'procurement_status' => 'ordered', ]); //added this line 

            $lpo->logStatusChange('draft', session('user_id'), 'Purchase Order created from Store Requisition #' . $storeRequisition->id);

            return $lpo;
        });

        return response()->json(['success' => true, 'id' => $lpo->local_purchase_order_id]);
    }

    // public function rejectRequisition(Request $request, StoreRequisition $storeRequisition): JsonResponse
    // {
    //     abort_if($storeRequisition->localPurchaseOrder, 409, 'Cannot reject — a Purchase Order already exists for this requisition.');

    //     $data = $request->validate(['reason' => 'required|string|max:255']);

    //     DB::transaction(function () use ($storeRequisition, $data) {
    //         $storeRequisition->items()->update([
    //             'procurement_status' => 'rejected',
    //             'rejection_reason' => $data['reason'],
    //         ]);

    //         $storeRequisition->update([
    //             'procurement_status' => 'rejected',
    //             'rejection_reason' => $data['reason'],
    //         ]);
    //     });

    //     return response()->json(['success' => true]);
    // }

    public function previewForSupplier(Request $request, StoreRequisition $storeRequisition): View
    {
        $storeRequisition->load(['items.item.unitOfMeasure', 'subdepartment.department.branch.company', 'preparedBy']);

        $supplier = $request->query('supplier_id') ? Supplier::find($request->query('supplier_id')) : null;
        $branch = $storeRequisition->subdepartment?->department?->branch;
        $company = $branch?->company;

        $preparedByTitle = null;
        if ($storeRequisition->preparedBy?->privilege_id) {
            $preparedByTitle = \App\Models\UserPrivilege::find($storeRequisition->preparedBy->privilege_id)?->privilege_name;
        }

        $totals = [
            'units' => $storeRequisition->items->sum('units'),
            'items_per_unit' => $storeRequisition->items->sum('items_per_unit'),
            'quantity' => $storeRequisition->items->sum('quantity'),
        ];

        return view('templates.procurement.store_requisitions.preview', compact(
            'storeRequisition', 'supplier', 'branch', 'company', 'preparedByTitle', 'totals'
        ));
    }

    //Start of stage 2 draft, approve etc
    
    public function purchaseRequisitionList(): View
    {
        return $this->nicePage('templates.procurement.purchase_requisition.list', 'procurement.purchase-requisition', [
            'items' => LocalPurchaseOrder::with(['supplier', 'storeRequisition.subdepartment', 'createdBy'])
                ->where('procurement_subdepartment_id', session('active_subdepartment_id'))
                ->where('status', 'draft')
                ->orderByDesc('local_purchase_order_id')
                ->get(),
        ]);
    }

    public function editDraft(LocalPurchaseOrder $localPurchaseOrder): View
    {
        abort_unless($localPurchaseOrder->status === 'draft', 403, 'Only draft Purchase Orders can be edited.');
        abort_unless($localPurchaseOrder->procurement_subdepartment_id === session('active_subdepartment_id'), 403, 'This Purchase Order belongs to a different Sub Department.');

        $localPurchaseOrder->load(['items.item.unitOfMeasure', 'supplier', 'storeRequisition.subdepartment']);

        return $this->nicePage('templates.procurement.purchase_requisition.edit', 'procurement.purchase-requisition', [
            'lpo' => $localPurchaseOrder,
            'suppliers' => Supplier::orderBy('supplier_name')->get(),
        ]);
    }

    public function updateDraft(Request $request, LocalPurchaseOrder $localPurchaseOrder): JsonResponse
    {
        abort_unless($localPurchaseOrder->status === 'draft', 403, 'Only draft Purchase Orders can be edited.');

        $data = $request->validate([
            'supplier_id' => 'nullable|integer|exists:tbl_suppliers,id',
            'currency_type' => 'required|string|max:10',
            'requisition_description' => 'required|string|max:255',
            'vat_charges' => 'nullable|string|max:255',
            'transport_charges' => 'nullable|string|max:255',
            'labor_charges' => 'nullable|string|max:255',
            'bank_charges' => 'nullable|string|max:255',
            'freight_charges' => 'nullable|string|max:255',
            'other_charges' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.lpo_item_id' => 'required|integer|exists:tbl_local_purchase_order_items,lpo_item_id',
            'items.*.price' => 'required|numeric|min:0.01',
            'items.*.quantity_required' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($data, $localPurchaseOrder) {
            $localPurchaseOrder->update(collect($data)->except('items')->all());

            foreach ($data['items'] as $line) {
                $localPurchaseOrder->items()->where('lpo_item_id', $line['lpo_item_id'])->update([
                    'Price' => $line['price'],
                    'Quantity_Required' => $line['quantity_required'],
                    'Remain_Balance' => $line['quantity_required'],
                ]);
            }
        });

        return response()->json(['success' => true]);
    }

    public function submitForApproval(LocalPurchaseOrder $localPurchaseOrder): JsonResponse
    {
        abort_unless($localPurchaseOrder->status === 'draft', 403, 'Only draft Purchase Orders can be submitted.');

        $localPurchaseOrder->logStatusChange('pending_approval', session('user_id'));
        $localPurchaseOrder->update([
            'status' => 'pending_approval',
            'submitted_by_user_id' => session('user_id'),
            'submitted_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function approveLpoList(): View
    {
        return $this->nicePage('templates.procurement.approve_lpo.list', 'procurement.approve-lpo', [
            'items' => LocalPurchaseOrder::with(['supplier', 'storeRequisition.subdepartment', 'createdBy'])
                ->where('procurement_subdepartment_id', session('active_subdepartment_id'))
                ->where('status', 'pending_approval')
                ->orderByDesc('local_purchase_order_id')
                ->get(),
        ]);
    }

    public function approveLpo(Request $request, LocalPurchaseOrder $localPurchaseOrder): JsonResponse
    {
        abort_unless($localPurchaseOrder->status === 'pending_approval', 403, 'Only Purchase Orders pending approval can be approved.');

        $credentials = $request->validate(['username' => 'required|string', 'password' => 'required|string']);
        $approver = User::where('email', $credentials['username'])->first();

        if (! $approver || ! Hash::check($credentials['password'], $approver->password)) {
            return response()->json(['message' => 'Invalid username or password.'], 422);
        }

        if (! $approver->hasApprovalPermission('purchase_order_approval')) {
            return response()->json(['message' => 'This user is not authorized to approve Purchase Orders.'], 403);
        }

        $localPurchaseOrder->logStatusChange('approved', $approver->id);
        $localPurchaseOrder->update(['status' => 'approved', 'approved_by_user_id' => $approver->id, 'approved_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function finalList(): View
    {
        return $this->nicePage('templates.procurement.local_purchase_order.list', 'procurement.local-purchase-order', [
            'items' => LocalPurchaseOrder::with(['supplier', 'storeRequisition.subdepartment', 'approvedBy'])
                ->where('procurement_subdepartment_id', session('active_subdepartment_id'))
                ->where('status', 'approved')
                ->orderByDesc('local_purchase_order_id')
                ->get(),
        ]);
    }

    public function printLpo(LocalPurchaseOrder $localPurchaseOrder): View
    {
        $localPurchaseOrder->load(['items.item.unitOfMeasure', 'supplier', 'storeRequisition.subdepartment.department.branch.company', 'createdBy', 'approvedBy']);

        $branch = $localPurchaseOrder->storeRequisition?->subdepartment?->department?->branch;
        $itemsTotal = $localPurchaseOrder->items->sum(fn ($i) => $i->Quantity_Required * $i->Price);
        $otherSum = collect([$localPurchaseOrder->vat_charges, $localPurchaseOrder->transport_charges, $localPurchaseOrder->labor_charges, $localPurchaseOrder->bank_charges, $localPurchaseOrder->freight_charges, $localPurchaseOrder->other_charges])
            ->map(fn ($v) => (float) $v)->sum();

        return view('templates.procurement.local_purchase_order.print', [
            'lpo' => $localPurchaseOrder,
            'branch' => $branch,
            'company' => $branch?->company,
            'itemsTotal' => $itemsTotal,
            'grandTotal' => $itemsTotal + $otherSum,
        ]);
    }

    public function rejectRequisition(Request $request, StoreRequisition $storeRequisition): JsonResponse
    {
        abort_if($storeRequisition->localPurchaseOrder, 409, 'Cannot reject — a Purchase Order already exists for this requisition.');

        $data = $request->validate(['reason' => 'required|string|max:255']);

        DB::transaction(function () use ($storeRequisition, $data) {
            $storeRequisition->items()->update([
                'procurement_status' => 'rejected',
                'rejection_reason' => $data['reason'],
            ]);

            $storeRequisition->update([
                'procurement_status' => 'rejected',
                'rejection_reason' => $data['reason'],
                'procurement_subdepartment_id' => session('active_subdepartment_id'),
                'cancelled_by_user_id' => session('user_id'),
                'cancelled_at' => now(),
            ]);
        });

        return response()->json(['success' => true]);
    }
}