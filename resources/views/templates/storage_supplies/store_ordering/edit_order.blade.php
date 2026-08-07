@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="card mb-3">
    <div class="card-header">Edit Order #{{ $storeRequisition->id }} — Items Only</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Order Number</label>
                <input type="text" class="form-control" value="{{ $storeRequisition->id }}" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label">Order Date</label>
                <input type="text" class="form-control" value="{{ $storeRequisition->order_date }}" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label">Prepared By</label>
                <input type="text" class="form-control" value="{{ $storeRequisition->preparedBy->name ?? '—' }}" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label">Store Ordering</label>
                <input type="text" class="form-control" value="{{ $storeRequisition->subdepartment->Subdepartment_Name ?? '—' }}" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label">Priority Status</label>
                <input type="text" class="form-control" value="{{ ucfirst($storeRequisition->priority_status) }}" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label">Emergency Reason</label>
                <input type="text" class="form-control" value="{{ $storeRequisition->emergency_reason }}" disabled>
            </div>
            <div class="col-12">
                <label class="form-label">Order Description</label>
                <textarea class="form-control" rows="2" disabled>{{ $storeRequisition->order_description }}</textarea>
            </div>
            @if($storeRequisition->status === 'approved')
                <div class="col-md-4">
                    <label class="form-label">Approved By</label>
                    <input type="text" class="form-control" value="{{ $storeRequisition->approvedBy->name ?? '—' }}" disabled>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Approved At</label>
                    <input type="text" class="form-control" value="{{ $storeRequisition->approved_at }}" disabled>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        @include('templates.storage_supplies.partials.item_picker', [
            'itemCategories' => $itemCategories,
            'pickerEndpoint' => route('storage_supplies.store_ordering.items_picker'),
        ])
    </div>
    <div class="col-md-8">
        <div class="table-responsive">
            <table class="table table-hover" id="requisitionItemsTable" style="min-width: 900px;">
                <thead>
                    <tr>
                        <th style="min-width:60px;">S/N</th>
                        <th style="min-width:200px;">Item Name</th>
                        <th style="min-width:80px;">UoM</th>
                        <th style="min-width:90px;">Units</th>
                        <th style="min-width:120px;">Items per Unit</th>
                        <th style="min-width:90px;">Quantity</th>
                        <th style="min-width:220px;">Item Details</th>
                        <th style="min-width:80px;">Remove</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="text-end mt-3">
    <a href="{{ route('storage_supplies.store_ordering.pending_order') }}" class="btn btn-secondary">Cancel</a>
    <button type="button" class="btn btn-info text-white" id="js-save-items">Save Changes</button>
</div>
@endsection
@php
$existingItems = $storeRequisition->items->map(function ($line) {
    return [
        'id' => $line->item_id,
        'name' => $line->item->product_name ?? 'Unknown item',
        'units' => $line->units,
        'itemsPerUnit' => $line->items_per_unit,
        'details' => $line->item_details,
    ];
});
@endphp

<script>
window.storeOrderingExistingItems = @json($existingItems);

</script>
@section('scripts')
<script>
    window.storeOrderingEditRoutes = {
        update: '{{ route("storage_supplies.store_ordering.update_items", $storeRequisition->id) }}',
        pendingOrder: '{{ route("storage_supplies.store_ordering.pending_order") }}',
    };
</script>
<script src="{{ asset('assets/js/item-picker.js') }}"></script>
<script src="{{ asset('assets/js/store-ordering-edit.js') }}"></script>
@endsection