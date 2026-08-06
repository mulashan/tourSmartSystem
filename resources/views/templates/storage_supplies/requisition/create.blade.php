@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="card mb-3">
    <div class="card-header">New Requisition</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label">Document Number</label><input type="text" class="form-control" value="{{ $nextDocumentNumberPreview }}" disabled></div>
            <div class="col-md-4"><label class="form-label">Requisition Date</label><input type="text" class="form-control" value="{{ now()->toDateString() }}" disabled></div>
            <div class="col-md-4"><label class="form-label">Officer</label><input type="text" class="form-control" value="{{ session('user_name') }}" disabled></div>
            <div class="col-md-4"><label class="form-label">Store Requesting</label><input type="text" class="form-control" id="storeRequestingLabel" disabled></div>
            <div class="col-md-4">
                <label class="form-label">Requisition Description *</label>
                <textarea id="description" class="form-control" rows="1" required></textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">Store Issuing *</label>
                <select id="issuingSubdepartmentId" class="form-select" required>
                    <option value="">Select...</option>
                    @foreach($issuingSubdepartments as $sub)
                        <option value="{{ $sub->Subdepartment_ID }}">{{ $sub->Subdepartment_Name }}</option>
                    @endforeach
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
                    <tbody><tr class="js-empty-row"><td colspan="6" class="text-center text-muted">No items added</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="text-end mt-3">
        <a href="{{ route('storage_supplies.requisition.pending') }}" class="btn btn-secondary">Cancel</a>
        <button type="button" class="btn btn-info text-white" id="js-save-requisition">Save</button>
    </div>
</div>
@endsection

@section('scripts')
<script>
    window.requisitionRoutes = {
        store: '{{ route("storage_supplies.requisition.store") }}',
        itemsPicker: '{{ route("storage_supplies.requisition.items_picker") }}',
        pendingList: '{{ route("storage_supplies.requisition.pending") }}',
    };
</script>
<script src="{{ asset('assets/js/requisition-create.js') }}"></script>
@endsection