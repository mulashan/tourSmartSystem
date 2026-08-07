@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="card mb-3">
    <div class="card-header">New GRN</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label">Store Order Requisition No.</label><input type="text" class="form-control" value="{{ $lpo->store_requisition_id }}" disabled></div>
            <div class="col-md-4"><label class="form-label">Purchase Requisition No.</label><input type="text" class="form-control" value="{{ $lpo->local_purchase_order_id }}" disabled></div>
            <div class="col-md-4"><label class="form-label">Created Date</label><input type="text" class="form-control" value="{{ $lpo->created_at->format('Y-m-d H:i:s') }}" disabled></div>

            <div class="col-md-4"><label class="form-label">Store Requesting</label><input type="text" class="form-control" value="{{ $lpo->storeRequisition->subdepartment->Subdepartment_Name ?? '—' }}" disabled></div>
            <div class="col-md-4"><label class="form-label">Supplier</label><input type="text" class="form-control" value="{{ $lpo->supplier->supplier_name ?? '—' }}" disabled></div>
            <div class="col-md-4"><label class="form-label">Purchase Description</label><input type="text" class="form-control" value="{{ $lpo->requisition_description }}" disabled></div>

            <div class="col-md-6"><label class="form-label">Created By</label><input type="text" class="form-control" value="{{ $lpo->createdBy->name ?? '—' }}" disabled></div>

            <div class="col-md-4">
                <label class="form-label">Delivery Note Number *</label>
                <input type="text" id="deliveryNoteNumber" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Attachment</label>
                <input type="file" id="deliveryNoteAttachment" class="form-control">
            </div>

            <div class="col-md-4">
                <label class="form-label">Invoice Number *</label>
                <input type="text" id="invoiceNumber" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Attachment</label>
                <input type="file" id="invoiceAttachment" class="form-control">
            </div>

            <div class="col-md-6">
                <label class="form-label">Delivery Date *</label>
                <input type="date" id="deliveryDate" class="form-control" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Delivery Person</label>
                <input type="text" id="deliveryPerson" class="form-control">
            </div>
        </div>
    </div>
</div>

<div class="settings-panel-head"><h2>LPO Items</h2></div>

<div class="table-responsive">
    <table class="table table-hover" id="grnItemsTable" style="min-width:1100px;">
        <thead>
            <tr>
                <th>S/N</th><th>Item Name</th><th>Units</th><th>Items per Unit</th><th>Quantity required</th>
                <th>Buying Price (Tshs)</th><th>Quantity Received</th><th>Amount (Tshs)</th><th>Remarks</th><th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lpo->items as $i => $line)
                @php $qtyRequired = (int) $line->Quantity_Required; @endphp
                <tr data-lpo-item-id="{{ $line->lpo_item_id }}" data-item-name="{{ $line->item->product_name }}" data-qty-required="{{ $qtyRequired }}" data-price="{{ $line->Price }}">
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $line->item->product_name ?? '—' }}</td>
                    <td>{{ $line->Containers_Required }}</td>
                    <td>{{ $line->Items_Per_Container_Required }}</td>
                    <td>{{ $qtyRequired }}</td>
                    <td>{{ number_format($line->Price, 2) }}</td>
                    <td class="js-received-qty">0</td>
                    <td class="js-received-amount">0</td>
                    <td><input type="text" class="form-control form-control-sm js-item-remarks"></td>
                    <td><button type="button" class="btn btn-sm btn-dark js-open-batch">Batch</button></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="text-end mt-3">
    <a href="{{ route('storage_supplies.grn.new') }}" class="btn btn-secondary">Cancel</a>
    <button type="button" class="btn btn-info text-white" id="js-submit-inspection">Submit for Approval</button>
</div>

<!-- Batch Modal -->
<div class="modal fade" id="batchModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Batch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-8"><label class="form-label">Item Name</label><input type="text" class="form-control" id="batchItemName" disabled></div>
                    <div class="col-md-4"><label class="form-label">Purchased Quantity</label><input type="text" class="form-control" id="batchQtyRequired" disabled></div>
                </div>

                <h6>Batch Details</h6>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Batch Number *</label><input type="text" id="batchNumber" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Units *</label><input type="number" min="1" id="batchUnits" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Items per Unit *</label><input type="number" min="1" id="batchItemsPerUnit" class="form-control" value="1"></div>

                    <div class="col-md-4"><label class="form-label">Quantity</label><input type="text" id="batchQuantityPreview" class="form-control" disabled value="0"></div>
                    <div class="col-md-4"><label class="form-label">Buying Price *</label><input type="number" min="0" step="0.01" id="batchBuyingPrice" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Manufacture Date *</label><input type="date" id="batchManufactureDate" class="form-control" max="{{ now()->toDateString() }}"></div>

                    <div class="col-md-6"><label class="form-label">Expiry Date *</label><input type="date" id="batchExpiryDate" class="form-control" min="{{ now()->toDateString() }}"></div>
                    <div class="col-md-6"><label class="form-label">Received Date *</label><input type="date" id="batchReceivedDate" class="form-control" value="{{ now()->toDateString() }}"></div>
                </div>

                <div class="text-danger small mt-2" id="batchFormError"></div>

                <div class="settings-panel-head mt-4"><h6>Batch List</h6><button type="button" class="btn btn-sm btn-info text-white" id="js-add-batch-row">Add</button></div>

                <div class="table-responsive">
                    <table class="table table-sm" id="batchListTable" style="min-width:900px;">
                        <thead>
                            <tr>
                                <th>S/N</th><th>Batch No.</th><th>Units</th><th>Items per Unit</th><th>Quantity</th>
                                <th>Buying Price</th><th>Manufacture Date</th><th>Expiry Date</th><th>Received Date</th><th>Action</th>
                            </tr>
                        </thead>
                        <tbody><tr class="js-batch-empty-row"><td colspan="10" class="text-center text-muted">No data available</td></tr></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    window.grnRoutes = {
        store: '{{ route("storage_supplies.grn.store", $lpo->local_purchase_order_id) }}',
        listIndex: '{{ route("storage_supplies.grn.new") }}',
    };
</script>
<script src="{{ asset('assets/js/grn-create.js') }}"></script>
@endsection
