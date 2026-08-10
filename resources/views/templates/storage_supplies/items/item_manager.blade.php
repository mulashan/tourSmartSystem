@extends('templates.app')

@section('content')

@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="settings-panel-head">
    <h2>Item Manager</h2>
    <div>
        <button type="button" class="btn btn-outline-secondary me-2" id="js-add-category">
            <i class="bi bi-plus-lg"></i> New Item Category
        </button>
        <button type="button" class="btn btn-info text-white" id="js-add-item">
            <i class="bi bi-plus-lg"></i> New Item
        </button>
    </div>
</div>

<div id="item-table-wrapper">
    <div class="text-muted p-4">Loading...</div>
</div>

<div class="modal fade" id="itemModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <form id="itemForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="itemModalLabel">Add Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="itemId">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Product Name *</label>
                            <input type="text" id="productName" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Product Code Prefix</label>
                            <input type="text" id="productCodePrefix" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Product Code</label>
                            <input type="text" id="productCode" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Item Category *</label>
                            <select id="itemCategory" class="form-select" required>
                                <option value="">Select Category</option>
                                @foreach($itemCategories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Unit Of Measure</label>
                            <select id="unitOfMeasure" class="form-select">
                                <option value="">Select Unit</option>
                                @foreach($measuringUnits as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status *</label>
                            <select id="itemStatus" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Reorder Level</label>
                            <input type="number" min="0" id="reorderLevel" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Min Reorder Level</label>
                            <input type="number" min="0" id="minimumReorderLevel" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Max Reorder Level</label>
                            <input type="number" min="0" id="maximumReorderLevel" class="form-control">
                        </div>
                    </div>
                    <div class="text-danger small mt-2" id="itemFormError"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="categoryQuickAddModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="categoryQuickAddForm">
                <div class="modal-header">
                    <h5 class="modal-title">New Item Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" id="categoryName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Code</label>
                        <input type="text" id="categoryCode" class="form-control">
                    </div>
                    <div class="text-danger small" id="categoryFormError"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/js/item-manager.js') }}"></script>
@endsection
