@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="card mb-3">
    <div class="card-header">Edit Return #{{ $return->id }}</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Document Number</label>
                <input type="text" class="form-control" value="{{ $return->id }}" disabled>
            </div>
            <div class="col-md-3">
                <label class="form-label">Return Date *</label>
                <input type="date" id="returnDate" class="form-control" value="{{ $return->return_date }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Posted By</label>
                <input type="text" class="form-control" value="{{ $return->postedBy->name ?? '—' }}" disabled>
            </div>
            <div class="col-md-3">
                <label class="form-label">Store Returning</label>
                <input type="text" class="form-control" value="{{ $return->fromSubdepartment->Subdepartment_Name ?? '—' }}" disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label">Description *</label>
                <textarea id="description" class="form-control" rows="1" required>{{ $return->description }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Store Receiving</label>
                <input type="text" class="form-control" value="{{ $return->toSubdepartment->Subdepartment_Name ?? '—' }}" disabled>
                <small class="text-muted">Store Receiving cannot be changed after creation.</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        @include('templates.storage_supplies.partials.item_picker', [
            'itemCategories' => $itemCategories,
            'pickerEndpoint' => route('storage_supplies.return.items_picker'),
        ])
    </div>
    <div class="col-md-8">
        <div class="table-responsive">
            <table class="table table-hover" id="returnItemsTable" style="min-width:700px;">
                <thead><tr><th>S/N</th><th>Item Name</th><th>UoM</th><th>Store Balance</th><th>Quantity to Return</th><th>Remove</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="text-end mt-3">
    <a href="{{ route('storage_supplies.return.new') }}" class="btn btn-secondary">Cancel</a>
    <button type="button" class="btn btn-outline-primary" id="js-save-return">Save</button>
    <button type="button" class="btn btn-info text-white" id="js-save-submit-return">Save and Send for Approval</button>
</div>
@endsection

@php
$existingLines = $return->items->map(function ($line) use ($balances) {
    return [
        'id' => $line->item_id,
        'name' => $line->item->product_name ?? 'Unknown item',
        'uom' => $line->item->unitOfMeasure->name ?? '—',
        'balance' => $balances->get($line->item_id, $line->quantity),
        'quantity' => $line->quantity,
    ];
})->values();
@endphp

@section('scripts')
<script>
    window.returnRoutes = {
        update: '{{ route("storage_supplies.return.update", $return->id) }}',
        draftList: '{{ route("storage_supplies.return.new") }}',
    };
    window.returnExistingLines = @json($existingLines);
</script>
<script src="{{ asset('assets/js/item-picker.js') }}"></script>
<script src="{{ asset('assets/js/return-edit.js') }}"></script>
@endsection