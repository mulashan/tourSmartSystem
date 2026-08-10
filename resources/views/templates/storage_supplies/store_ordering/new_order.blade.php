@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="card mb-3">
    <div class="card-header">New Order</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Order Number</label>
                <input type="text" class="form-control" value="{{ $nextOrderNumberPreview }}" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label">Order Date</label>
                <input type="text" class="form-control" value="{{ now()->toDateString() }}" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label">Prepared By</label>
                <input type="text" class="form-control" value="{{ $preparedByName }}" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label">Store Ordering</label>
                <input type="text" class="form-control" id="storeOrderingLabel" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label">Priority Status</label>
                <select class="form-select" id="priorityStatus">
                    <option value="normal">Normal</option>
                    <option value="emergency">Emergency</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Emergency Reason</label>
                <textarea class="form-control" id="emergencyReason" rows="1"></textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Order Description</label>
                <textarea class="form-control" id="orderDescription" rows="1"></textarea>
            </div>
        </div>

        <div class="form-check mt-3">
            <input class="form-check-input" type="checkbox" id="userStoreOrder">
            <label class="form-check-label" for="userStoreOrder">User Store Order</label>
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
        <table class="table table-hover" id="requisitionItemsTable" data-enhance-table data-export-name="new-orders" style="min-width: 900px;" >
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
            <tbody>
                <tr class="js-empty-row"><td colspan="8" class="text-center text-muted">No data available</td></tr>
            </tbody>
        </table>
    </div>
</div>
</div>

<div class="text-end mt-3">
    <button type="button" class="btn btn-info text-white" id="js-submit-for-approval">Submit for Approval</button>
</div>
@endsection

@section('scripts')
<script>
    window.storeOrderingRoutes = {
        store: '{{ route("storage_supplies.store_ordering.store") }}',
        pendingOrder: '{{ route("storage_supplies.store_ordering.pending_order") }}',
    };
</script>
<script src="{{ asset('assets/js/item-picker.js') }}"></script>
<script src="{{ asset('assets/js/store-ordering.js') }}"></script>
<script src="{{ asset('assets/js/table-toolkit.js') }}"></script>
@endsection
