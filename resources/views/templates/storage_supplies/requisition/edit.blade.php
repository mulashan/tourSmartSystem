@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="card mb-3">
    <div class="card-header">Edit Requisition #{{ $requisition->id }}</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label">Document Number</label><input type="text" class="form-control" value="{{ $requisition->id }}" disabled></div>
            <div class="col-md-4"><label class="form-label">Requisition Date</label><input type="text" class="form-control" value="{{ $requisition->requisition_date }}" disabled></div>
            <div class="col-md-4"><label class="form-label">Officer</label><input type="text" class="form-control" value="{{ $requisition->officer->name ?? '—' }}" disabled></div>
            <div class="col-md-4"><label class="form-label">Store Requesting</label><input type="text" class="form-control" value="{{ $requisition->requestingSubdepartment->Subdepartment_Name ?? '—' }}" disabled></div>
            <div class="col-md-4"><label class="form-label">Requisition Description *</label><input type="text" class="form-control" id="description" value="{{ $requisition->description }}" required></div>
            <div class="col-md-4">
                <label class="form-label">Store Issuing</label>
                <input type="text" class="form-control" value="{{ $requisition->issuingSubdepartment->Subdepartment_Name ?? '—' }}" disabled>
                <small class="text-muted">Store Issuing cannot be changed after creation.</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="requisition-picker">
            <label class="text-danger">*</label>
            <select class="form-select mb-2" id="reqPickerCategory">
                <option value="">Item Category</option>
                @foreach($itemCategories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <input type="text" class="form-control mb-2" id="reqPickerSearch" placeholder="Item Name">
            <table class="table table-sm table-hover">
                <thead><tr><th>Item Name</th><th>Balance</th></tr></thead>
                <tbody id="reqPickerResults">
                    <tr><td colspan="2" class="text-muted text-center">Select a category or search to see items</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-md-8">
        <div class="table-responsive">
            <table class="table table-hover" id="requisitionItemsTable" style="min-width:700px;">
                <thead><tr><th>S/N</th><th>Item Name</th><th>UoM</th><th>Balance</th><th>Quantity Requested</th><th>Item Details</th><th>Remove</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="text-end mt-3">
    <a href="{{ route('storage_supplies.requisition.pending') }}" class="btn btn-secondary">Cancel</a>
    <button type="button" class="btn btn-info text-white" id="js-save-requisition">Save Changes</button>
</div>
@endsection

@php
$existingLines = $requisition->items->map(function ($line) use ($balances) {
    return [
        'id' => $line->item_id,
        'name' => $line->item->product_name ?? 'Unknown item',
        'uom' => $line->item->unitOfMeasure->name ?? '—',
        'balance' => $balances->get($line->item_id, $line->quantity_requested),
        'quantity' => $line->quantity_requested,
        'details' => $line->item_details,
    ];
})->values();
@endphp

@section('scripts')
<script>
    window.requisitionRoutes = {
        update: '{{ route("storage_supplies.requisition.update", $requisition->id) }}',
        itemsPicker: '{{ route("storage_supplies.requisition.items_picker") }}',
        pendingList: '{{ route("storage_supplies.requisition.pending") }}',
    };
    window.requisitionExistingIssuingId = {{ $requisition->issuing_subdepartment_id }};
    window.requisitionExistingLines = @json($existingLines);
</script>
<script src="{{ asset('assets/js/requisition-edit.js') }}"></script>
@endsection