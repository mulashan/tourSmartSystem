@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="card mb-3">
    <div class="card-header">New Service Use</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Document Number</label>
                <input type="text" class="form-control" value="{{ $nextDocumentNumberPreview }}" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label">Requisition Date *</label>
                <input type="date" id="requisitionDate" class="form-control" value="{{ now()->toDateString() }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Officer</label>
                <input type="text" class="form-control" value="{{ session('user_name') }}" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label">Store</label>
                <input type="text" class="form-control" id="storeLabel" disabled>
            </div>
            <div class="col-md-8">
                <label class="form-label">Reason *</label>
                <textarea id="reason" class="form-control" rows="1" required></textarea>
            </div>
        </div>

        <div class="text-end mt-3">
            <button type="button" class="btn btn-dark" id="js-lock-header">Continue &rarr; Select Items</button>
        </div>
    </div>
</div>

<div id="itemSelectionSection" class="d-none">
    <div class="row g-3">
        <div class="col-md-4">
            <div class="service-use-picker">
                <label class="text-danger">*</label>
                <select class="form-select mb-2" id="suPickerCategory">
                    <option value="">Item Category</option>
                    @foreach($itemCategories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                <input type="text" class="form-control mb-2" id="suPickerSearch" placeholder="Item Name">
                <table class="table table-sm table-hover">
                    <thead><tr><th>Item Name</th><th>Balance</th></tr></thead>
                    <tbody id="suPickerResults">
                        <tr><td colspan="2" class="text-muted text-center">Select a category or search to see items</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-md-8">
            <div class="table-responsive">
                <table class="table table-hover" id="serviceUseItemsTable" style="min-width:700px;">
                    <thead><tr><th>S/N</th><th>Item Name</th><th>UoM</th><th>Store Balance</th><th>Quantity</th><th>Remove</th></tr></thead>
                    <tbody><tr class="js-empty-row"><td colspan="6" class="text-center text-muted">No items added</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="text-end mt-3">
        <button type="button" class="btn btn-secondary" id="js-cancel-service-use">Cancel</button>
        <button type="button" class="btn btn-info text-white" id="js-submit-service-use">Submit</button>
    </div>
</div>
@endsection

@section('scripts')
<script>
    window.serviceUseRoutes = {
        store: '{{ route("storage_supplies.service_use.store") }}',
        itemsPicker: '{{ route("storage_supplies.service_use.items_picker") }}',
        previousList: '{{ route("storage_supplies.service_use.previous") }}',
    };
</script>
<script src="{{ asset('assets/js/service-use-create.js') }}"></script>
@endsection