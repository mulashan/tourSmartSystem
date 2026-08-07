@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="card mb-3">
    <div class="card-header">New Stock Adjustment</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Adjustment Number</label>
                <input type="text" class="form-control" value="{{ $nextAdjustmentNumberPreview }}" disabled>
            </div>
            <div class="col-md-3">
                <label class="form-label">Adjustment Date *</label>
                <input type="date" id="adjustmentDate" class="form-control" value="{{ now()->toDateString() }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Adjustment Officer</label>
                <input type="text" class="form-control" value="{{ session('user_name') }}" disabled>
            </div>
            <div class="col-md-3">
                <label class="form-label">Adjustment Store</label>
                <input type="text" class="form-control" id="storeLabel" disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label">Adjustment Description *</label>
                <textarea id="description" class="form-control" rows="1" required></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Adjustment Reason *</label>
                <select id="reason" class="form-select" required>
                    <option value="">Select...</option>
                    <option value="add_stock_balance">Add Stock Balance</option>
                    <option value="expired_dump_broken">Expired / Dump / Broken</option>
                </select>
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
            <div class="adjustment-picker">
                <label class="text-danger">*</label>
                <select class="form-select mb-2" id="adjPickerCategory">
                    <option value="">Item Category</option>
                    @foreach($itemCategories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                <input type="text" class="form-control mb-2" id="adjPickerSearch" placeholder="Item Name">
                <table class="table table-sm table-hover">
                    <thead><tr><th>Item Name</th><th id="adjPickerBalanceHeader">Balance</th></tr></thead>
                    <tbody id="adjPickerResults">
                        <tr><td colspan="2" class="text-muted text-center">Select a category or search to see items</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-md-8">
            <div class="table-responsive">
                <!-- Add Stock Balance mode -->
                <table class="table table-hover d-none" id="addItemsTable" style="min-width:1000px;">
                    <thead><tr><th>S/N</th><th>Item Name</th><th>UoM</th><th>Quantity</th><th>Amount</th><th>Action</th><th>Remove</th></tr></thead>
                    <tbody><tr class="js-empty-row"><td colspan="7" class="text-center text-muted">No items added</td></tr></tbody>
                </table>

                <!-- Expired/Dump/Broken mode -->
                <table class="table table-hover d-none" id="deductItemsTable" style="min-width:700px;">
                    <thead><tr><th>S/N</th><th>Item Name</th><th>UoM</th><th>Store Balance</th><th>Quantity</th><th>Remove</th></tr></thead>
                    <tbody><tr class="js-empty-row"><td colspan="6" class="text-center text-muted">No items added</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="text-end mt-3">
        <button type="button" class="btn btn-secondary" id="js-cancel-adjustment">Cancel</button>
        <button type="button" class="btn btn-outline-primary" id="js-save-adjustment">Save</button>
        <button type="button" class="btn btn-info text-white" id="js-save-submit-adjustment">Save and Submit for Approval</button>
    </div>
</div>

@include('templates.storage_supplies.grn_without_po.partials.batch_modal')
@endsection

@section('scripts')
<script>
    window.stockAdjustmentRoutes = {
        store: '{{ route("storage_supplies.stock_adjustment.store") }}',
        itemsPicker: '{{ route("storage_supplies.stock_adjustment.items_picker") }}',
        draftList: '{{ route("storage_supplies.stock_adjustment.new") }}',
    };
</script>
<script src="{{ asset('assets/js/stock-adjustment-create.js') }}"></script>
@endsection