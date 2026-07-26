@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="card mb-3">
    <div class="card-header">New Purchase Requisition</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Store Requesting *</label>
                <input type="text" class="form-control" value="{{ $storeRequisition->subdepartment->Subdepartment_Name ?? '—' }}" disabled>
            </div>
            <div class="col-md-3">
                <label class="form-label">Supplier</label>
                <select id="supplierId" class="form-select">
                    <option value="">Select...</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->supplier_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Requisition Description *</label>
                <textarea id="requisitionDescription" class="form-control" rows="1" required></textarea>
            </div>
            <div class="col-md-3">
                <label class="form-label">Currency Type *</label>
                <select id="currencyType" class="form-select" required>
                    <option value="Tshs">Tshs</option>
                    <option value="USD">USD</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">Other Cost ({{ '' }}<span id="currencyLabel">Tshs</span>)</div>
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

<div class="settings-panel-head"><h2>Order Items</h2></div>

<div class="table-responsive">
    <table class="table table-hover" id="poItemsTable" style="min-width: 1100px;">
        <thead>
            <tr>
                <th>S/N</th><th>Item Name</th><th>Item Details</th><th>UoM</th>
                <th>Units</th><th>Items per Unit</th><th>Quantity</th>
                <th>Unit Price</th><th>Total Amount</th>
                <th><input type="checkbox" id="js-select-all" checked> Include</th>
            </tr>
        </thead>
        <tbody>
            @foreach($storeRequisition->items as $i => $line)
                <tr data-requisition-item-id="{{ $line->id }}">
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $line->item->product_name ?? '—' }}</td>
                    <td>{{ $line->item_details }}</td>
                    <td>{{ $line->item->unitOfMeasure->name ?? '-' }}</td>
                    <td><input type="number" min="1" class="form-control form-control-sm js-po-units" value="{{ $line->units }}"></td>
                    <td><input type="number" min="1" class="form-control form-control-sm js-po-per-unit" value="{{ $line->items_per_unit }}"></td>
                    <td class="js-po-quantity">{{ $line->units * $line->items_per_unit }}</td>
                    <td><input type="number" min="0" step="0.01" class="form-control form-control-sm js-po-price" placeholder="0.00"></td>
                    <td class="js-po-total">0</td>
                    <td class="text-center"><input type="checkbox" class="js-po-include" checked></td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="8" class="text-end fw-bold">Total</td>
                <td class="fw-bold" id="js-po-grand-items-total">0</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="card mt-3" style="max-width: 500px; margin-left: auto;">
    <div class="card-body">
        <div class="d-flex justify-content-between"><span>Other Charges</span><strong id="js-summary-other">0</strong></div>
        <div class="d-flex justify-content-between"><span>VAT</span><strong id="js-summary-vat">0</strong></div>
        <hr>
        <div class="d-flex justify-content-between text-danger fw-bold"><span>Grand Total (<span class="js-currency-label">Tshs</span>)</span><strong id="js-summary-grand-total">0</strong></div>
    </div>
</div>

<div class="text-end mt-3">
    <a href="{{ route('procurement.store_requisitions.index') }}" class="btn btn-secondary">Cancel</a>
    <button type="button" class="btn btn-danger" id="js-reject-requisition">Reject</button>
    <button type="button" class="btn btn-info text-white" id="js-save-draft">Save</button>
</div>
@endsection

@section('scripts')
<script>
    window.procurementRoutes = {
        storePo: '{{ route("procurement.store_requisitions.store_po", $storeRequisition->id) }}',
        reject: '{{ route("procurement.store_requisitions.reject", $storeRequisition->id) }}',
        listIndex: '{{ route("procurement.store_requisitions.index") }}',
    };
</script>
<script src="{{ asset('assets/js/create-purchase-order.js') }}"></script>
@endsection