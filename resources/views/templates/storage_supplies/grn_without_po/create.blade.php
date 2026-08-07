@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="card mb-3">
    <div class="card-header">New GRN — Without Purchase Order</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Supplier</label>
                <select id="supplierId" class="form-select">
                    <option value="">Select...</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->supplier_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3"><label class="form-label">Purchase Description</label><input type="text" id="purchaseDescription" class="form-control"></div>
            <div class="col-md-3">
                <label class="form-label">Delivery Note Number *</label>
                <input type="text" id="deliveryNoteNumber" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Attachment</label>
                <input type="file" id="deliveryNoteAttachment" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Invoice Number *</label>
                <input type="text" id="invoiceNumber" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Attachment</label>
                <input type="file" id="invoiceAttachment" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Delivery Date *</label>
                <input type="date" id="deliveryDate" class="form-control" value="{{ now()->toDateString() }}" required>
            </div>
            <div class="col-md-3"><label class="form-label">Delivery Person</label><input type="text" id="deliveryPerson" class="form-control"></div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">Other Cost</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label">VAT</label><input type="number" min="0" step="0.01" id="vatCharges" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Transportation Cost</label><input type="number" min="0" step="0.01" id="transportCharges" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Labour Charges</label><input type="number" min="0" step="0.01" id="laborCharges" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Bank Charges</label><input type="number" min="0" step="0.01" id="bankCharges" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Freight Charges</label><input type="number" min="0" step="0.01" id="freightCharges" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Other Charges</label><input type="number" min="0" step="0.01" id="otherCharges" class="form-control"></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        @include('templates.storage_supplies.partials.item_picker', [
            'itemCategories' => $itemCategories,
            'pickerEndpoint' => route('storage_supplies.grn_without_po.items_picker'),
        ])
    </div>
    <div class="col-md-8">
        <div class="table-responsive">
            <table class="table table-hover" id="grnItemsTable" style="min-width:1000px;">
                <thead>
                    <tr>
                        <th>S/N</th><th>Item Name</th><th>UoM</th><th>Quantity Received</th>
                        <th>Amount</th><th>Remarks</th><th>Action</th><th>Remove</th>
                    </tr>
                </thead>
                <tbody><tr class="js-empty-row"><td colspan="8" class="text-center text-muted">No items added</td></tr></tbody>
            </table>
        </div>
    </div>
</div>

<div class="text-end mt-3">
    <a href="{{ route('storage_supplies.grn_without_po.pending') }}" class="btn btn-secondary">Cancel</a>
    <button type="button" class="btn btn-info text-white" id="js-submit-grn" disabled>Submit for Approval</button>
</div>

@include('templates.storage_supplies.grn_without_po.partials.batch_modal')
@endsection

@section('scripts')
<script>
    window.grnWithoutPoRoutes = {
        store: '{{ route("storage_supplies.grn_without_po.store") }}',
        pendingList: '{{ route("storage_supplies.grn_without_po.pending") }}',
    };
</script>
<script src="{{ asset('assets/js/item-picker.js') }}"></script>
<script src="{{ asset('assets/js/grn-without-po-create.js') }}"></script>
@endsection