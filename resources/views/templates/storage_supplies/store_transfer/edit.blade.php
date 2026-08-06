@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="card mb-3">
    <div class="card-header">Edit Transfer #{{ $transfer->id }}</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Transfer Date</label>
                <input type="text" class="form-control" value="{{ $transfer->transfer_date }}" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label">Transfer To *</label>
                <select id="toSubdepartmentId" class="form-select" required>
                    <option value="">Select...</option>
                    @foreach($toSubdepartments as $sub)
                        <option value="{{ $sub->Subdepartment_ID }}" {{ $sub->Subdepartment_ID == $transfer->to_subdepartment_id ? 'selected' : '' }}>{{ $sub->Subdepartment_Name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Description</label>
                <input type="text" id="description" class="form-control" value="{{ $transfer->description }}">
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        @include('templates.storage_supplies.partials.item_picker', [
            'itemCategories' => $itemCategories,
            'pickerEndpoint' => route('storage_supplies.store_transfer.items_picker'),
        ])
    </div>
    <div class="col-md-8">
        <div class="table-responsive">
            <table class="table table-hover" id="transferItemsTable" style="min-width:800px;">
                <thead><tr><th>S/N</th><th>Item Name</th><th>UoM</th><th>Store Balance</th><th>Quantity to Transfer</th><th>Remove</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="text-end mt-3">
    <a href="{{ route('storage_supplies.store_transfer.draft') }}" class="btn btn-secondary">Cancel</a>
    <button type="button" class="btn btn-outline-primary" id="js-save-draft">Save Changes</button>
</div>
@endsection

@php
$existingLines = $transfer->items->map(function ($line) use ($balances) {
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
    window.storeTransferRoutes = {
        update: '{{ route("storage_supplies.store_transfer.update", $transfer->id) }}',
        draftList: '{{ route("storage_supplies.store_transfer.draft") }}',
    };
    window.storeTransferExistingLines = @json($existingLines);
</script>
<script src="{{ asset('assets/js/item-picker.js') }}"></script>
<script src="{{ asset('assets/js/store-transfer-edit.js') }}"></script>
@endsection