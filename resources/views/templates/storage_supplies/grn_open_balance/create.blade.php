@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="card mb-3">
    <div class="card-header">New GRN — Open Balance / Physical Count</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label">Creation Date *</label><input type="date" id="creationDate" class="form-control" value="{{ now()->toDateString() }}" required></div>
            <div class="col-md-8"><label class="form-label">Description</label><input type="text" id="description" class="form-control"></div>
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
                <tbody><tr class="js-empty-row"><td colspan="8" class="text-center text-muted">No items added</td></tr></tbody>
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

@section('scripts')
<script>
    window.grnOpenBalanceRoutes = {
        store: '{{ route("storage_supplies.grn_open_balance.store") }}',
        newList: '{{ route("storage_supplies.grn_open_balance.new") }}',
    };
</script>
<script src="{{ asset('assets/js/item-picker.js') }}"></script>
<script src="{{ asset('assets/js/grn-open-balance-create.js') }}"></script>
@endsection