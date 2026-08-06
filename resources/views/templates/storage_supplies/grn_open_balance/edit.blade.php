@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="card mb-3">
    <div class="card-header">Edit GRN #{{ $grn->id }} — Open Balance / Physical Count</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label">Creation Date *</label><input type="date" id="creationDate" class="form-control" value="{{ $grn->creation_date }}" required></div>
            <div class="col-md-8"><label class="form-label">Description</label><input type="text" id="description" class="form-control" value="{{ $grn->description }}"></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        @include('templates.storage_supplies.partials.item_picker', [
            'itemCategories' => $itemCategories,
            'pickerEndpoint' => route('storage_supplies.grn_open_balance.items_picker'),
        ])
    </div>
    <div class="col-md-8">
        <div class="table-responsive">
            <table class="table table-hover" id="grnItemsTable" style="min-width:1000px;">
                <thead><tr><th>S/N</th><th>Item Name</th><th>UoM</th><th>Quantity</th><th>Amount</th><th>Remarks</th><th>Action</th><th>Remove</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="text-end mt-3">
    <a href="{{ route('storage_supplies.grn_open_balance.new') }}" class="btn btn-secondary">Cancel</a>
    <button type="button" class="btn btn-outline-primary" id="js-save-draft">Save</button>
    <button type="button" class="btn btn-info text-white" id="js-save-submit">Save and Submit for Approval</button>
</div>

@include('templates.storage_supplies.grn_open_balance.partials.batch_modal')
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
    window.grnOpenBalanceRoutes = {
        update: '{{ route("storage_supplies.grn_open_balance.update", $grn->id) }}',
        newList: '{{ route("storage_supplies.grn_open_balance.new") }}',
    };
    window.grnOpenBalanceExistingItems = @json($existingItems);
</script>
<script src="{{ asset('assets/js/item-picker.js') }}"></script>
<script src="{{ asset('assets/js/grn-open-balance-edit.js') }}"></script>
@endsection