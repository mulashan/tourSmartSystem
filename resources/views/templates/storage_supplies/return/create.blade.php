@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="card mb-3">
    <div class="card-header">New Return</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Document Number</label>
                <input type="text" class="form-control" value="{{ $nextDocumentNumberPreview }}" disabled>
            </div>
            <div class="col-md-3">
                <label class="form-label">Return Date *</label>
                <input type="date" id="returnDate" class="form-control" value="{{ now()->toDateString() }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Posted By</label>
                <input type="text" class="form-control" value="{{ session('user_name') }}" disabled>
            </div>
            <div class="col-md-3">
                <label class="form-label">Store Returning</label>
                <input type="text" class="form-control" id="storeReturningLabel" disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label">Description *</label>
                <textarea id="description" class="form-control" rows="1" required></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Store Receiving *</label>
                <select id="toSubdepartmentId" class="form-select" required>
                    <option value="">Select...</option>
                    @foreach($toSubdepartments as $sub)
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
            @include('templates.storage_supplies.partials.item_picker', [
                'itemCategories' => $itemCategories,
                'pickerEndpoint' => route('storage_supplies.return.items_picker'),
            ])
        </div>
        <div class="col-md-8">
            <div class="table-responsive">
                <table class="table table-hover" id="returnItemsTable" style="min-width:700px;">
                    <thead><tr><th>S/N</th><th>Item Name</th><th>UoM</th><th>Store Balance</th><th>Quantity to Return</th><th>Remove</th></tr></thead>
                    <tbody><tr class="js-empty-row"><td colspan="6" class="text-center text-muted">No items added</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="text-end mt-3">
        <button type="button" class="btn btn-secondary" id="js-cancel-return">Cancel</button>
        <button type="button" class="btn btn-outline-primary" id="js-save-return">Save</button>
        <button type="button" class="btn btn-info text-white" id="js-save-submit-return">Save and Send for Approval</button>
    </div>
</div>
@endsection

@section('scripts')
<script>
    window.returnRoutes = {
        store: '{{ route("storage_supplies.return.store") }}',
        draftList: '{{ route("storage_supplies.return.new") }}',
    };
</script>
<script src="{{ asset('assets/js/item-picker.js') }}"></script>
<script src="{{ asset('assets/js/return-create.js') }}"></script>
@endsection