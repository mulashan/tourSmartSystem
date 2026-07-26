@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="card mb-3">
    <div class="card-header">Purchase Requisition — LPO #{{ $lpo->local_purchase_order_id }}</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Store Requesting</label>
                <input type="text" class="form-control" value="{{ $lpo->storeRequisition->subdepartment->Subdepartment_Name ?? '—' }}" disabled>
            </div>
            <div class="col-md-3">
                <label class="form-label">Supplier</label>
                <select id="supplierId" class="form-select">
                    <option value="">Select...</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ $lpo->supplier_id == $supplier->id ? 'selected' : '' }}>{{ $supplier->supplier_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Requisition Description *</label>
                <textarea id="requisitionDescription" class="form-control" rows="1">{{ $lpo->requisition_description }}</textarea>
            </div>
            <div class="col-md-3">
                <label class="form-label">Currency Type *</label>
                <select id="currencyType" class="form-select">
                    <option value="Tshs" {{ $lpo->currency_type === 'Tshs' ? 'selected' : '' }}>Tshs</option>
                    <option value="USD" {{ $lpo->currency_type === 'USD' ? 'selected' : '' }}>USD</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">Other Cost</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3"><label class="form-label">VAT</label><input type="number" step="0.01" id="vatCharges" class="form-control" value="{{ $lpo->vat_charges }}"></div>
            <div class="col-md-3"><label class="form-label">Transportation</label><input type="number" step="0.01" id="transportCharges" class="form-control" value="{{ $lpo->transport_charges }}"></div>
            <div class="col-md-3"><label class="form-label">Labour</label><input type="number" step="0.01" id="laborCharges" class="form-control" value="{{ $lpo->labor_charges }}"></div>
            <div class="col-md-3"><label class="form-label">Bank</label><input type="number" step="0.01" id="bankCharges" class="form-control" value="{{ $lpo->bank_charges }}"></div>
            <div class="col-md-3"><label class="form-label">Freight</label><input type="number" step="0.01" id="freightCharges" class="form-control" value="{{ $lpo->freight_charges }}"></div>
            <div class="col-md-3"><label class="form-label">Other</label><input type="number" step="0.01" id="otherCharges" class="form-control" value="{{ $lpo->other_charges }}"></div>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover" id="lpoItemsTable" style="min-width:800px;">
        <thead><tr><th>S/N</th><th>Item Name</th><th>UoM</th><th>Quantity</th><th>Unit Price</th><th>Total</th></tr></thead>
        <tbody>
            @foreach($lpo->items as $i => $line)
                <tr data-lpo-item-id="{{ $line->lpo_item_id }}">
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $line->item->product_name ?? '—' }}</td>
                    <td>{{ $line->item->unitOfMeasure->name ?? '-' }}</td>
                    <td><input type="number" min="1" class="form-control form-control-sm js-qty" value="{{ $line->Quantity_Required }}"></td>
                    <td><input type="number" min="0" step="0.01" class="form-control form-control-sm js-price" value="{{ $line->Price }}"></td>
                    <td class="js-total">0</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot><tr><td colspan="5" class="text-end fw-bold">Grand Total</td><td class="fw-bold" id="js-grand-total">0</td></tr></tfoot>
    </table>
</div>

<div class="text-end mt-3">
    <a href="{{ route('procurement.purchase_requisition.index') }}" class="btn btn-secondary">Cancel</a>
    <button type="button" class="btn btn-outline-primary" id="js-save-draft">Save</button>
    <button type="button" class="btn btn-info text-white" id="js-submit-approval">Submit for Approval</button>
</div>
@endsection

@section('scripts')
<script>
    window.lpoRoutes = {
        update: '{{ route("procurement.purchase_requisition.update", $lpo->local_purchase_order_id) }}',
        submit: '{{ route("procurement.purchase_requisition.submit", $lpo->local_purchase_order_id) }}',
        listIndex: '{{ route("procurement.purchase_requisition.index") }}',
    };
</script>
<script src="{{ asset('assets/js/purchase-requisition-edit.js') }}"></script>
@endsection