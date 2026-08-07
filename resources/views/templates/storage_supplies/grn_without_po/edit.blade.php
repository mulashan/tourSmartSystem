@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="card mb-3">
    <div class="card-header">Edit GRN #{{ $grn->id }} — Without Purchase Order</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Supplier</label>
                <select id="supplierId" class="form-select">
                    <option value="">Select...</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ $grn->supplier_id == $supplier->id ? 'selected' : '' }}>{{ $supplier->supplier_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3"><label class="form-label">Purchase Description</label><input type="text" id="purchaseDescription" class="form-control" value="{{ $grn->purchase_description }}"></div>
            <div class="col-md-3"><label class="form-label">Delivery Note Number *</label><input type="text" id="deliveryNoteNumber" class="form-control" value="{{ $grn->delivery_note_number }}" required></div>
            <div class="col-md-3"><label class="form-label">Invoice Number *</label><input type="text" id="invoiceNumber" class="form-control" value="{{ $grn->invoice_number }}" required></div>
            <div class="col-md-3"><label class="form-label">Delivery Date *</label><input type="date" id="deliveryDate" class="form-control" value="{{ $grn->delivery_date }}" required></div>
            <div class="col-md-3"><label class="form-label">Delivery Person</label><input type="text" id="deliveryPerson" class="form-control" value="{{ $grn->delivery_person }}"></div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">Other Cost</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label">VAT</label><input type="number" min="0" step="0.01" id="vatCharges" class="form-control" value="{{ $grn->vat_charges }}"></div>
            <div class="col-md-4"><label class="form-label">Transportation Cost</label><input type="number" min="0" step="0.01" id="transportCharges" class="form-control" value="{{ $grn->transport_charges }}"></div>
            <div class="col-md-4"><label class="form-label">Labour Charges</label><input type="number" min="0" step="0.01" id="laborCharges" class="form-control" value="{{ $grn->labor_charges }}"></div>
            <div class="col-md-4"><label class="form-label">Bank Charges</label><input type="number" min="0" step="0.01" id="bankCharges" class="form-control" value="{{ $grn->bank_charges }}"></div>
            <div class="col-md-4"><label class="form-label">Freight Charges</label><input type="number" min="0" step="0.01" id="freightCharges" class="form-control" value="{{ $grn->freight_charges }}"></div>
            <div class="col-md-4"><label class="form-label">Other Charges</label><input type="number" min="0" step="0.01" id="otherCharges" class="form-control" value="{{ $grn->other_charges }}"></div>
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
                    <tr><th>S/N</th><th>Item Name</th><th>UoM</th><th>Quantity Received</th><th>Amount</th><th>Remarks</th><th>Action</th><th>Remove</th></tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="text-end mt-3">
    <a href="{{ route('storage_supplies.grn_without_po.pending') }}" class="btn btn-secondary">Cancel</a>
    <button type="button" class="btn btn-info text-white" id="js-submit-grn">Save Changes</button>
</div>

@include('templates.storage_supplies.grn_without_po.partials.batch_modal')
@endsection

@php
$existingItems = $grn->items->map(function ($line) {
    return [
        'id' => $line->item_id,
        'name' => $line->item->product_name ?? 'Unknown item',
        'uom' => $line->item->unitOfMeasure->name ?? '—',
        'remarks' => $line->remarks,
        'batches' => $line->batches->map(function ($b) {
            return [
                'batch_number' => $b->batch_number,
                'units' => $b->units,
                'items_per_unit' => $b->items_per_unit,
                'quantity' => $b->quantity,
                'buying_price' => $b->buying_price,
                'manufacture_date' => optional($b->manufacture_date)->toDateString(),
                'expiry_date' => optional($b->expiry_date)->toDateString(),
                'received_date' => optional($b->received_date)->toDateString(),
            ];
        })->values(),
    ];
})->values();
@endphp
@section('scripts')
<script>
    window.grnWithoutPoRoutes = {
        update: '{{ route("storage_supplies.grn_without_po.update", $grn->id) }}',
        pendingList: '{{ route("storage_supplies.grn_without_po.pending") }}',
    };
    
    window.grnWithoutPoExistingItems = @json($existingItems);
</script>
<script src="{{ asset('assets/js/item-picker.js') }}"></script>
<script src="{{ asset('assets/js/grn-without-po-edit.js') }}"></script>
@endsection