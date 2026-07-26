@extends('templates.app')

@section('content')
<div class="settings-shell">
    <aside class="settings-sidebar">
        @foreach($lookupItems as $key => $item)
            <a href="#" class="settings-nav-link {{ $loop->first ? 'active' : '' }}" data-key="{{ $key }}" data-kind="lookup">
                {{ $item['label'] }}
            </a>
        @endforeach
        @foreach($customItems as $key => $item)
            <a href="#" class="settings-nav-link {{ empty($item['route']) ? 'disabled' : '' }}" data-key="{{ $key }}" data-kind="custom" @if(empty($item['route'])) title="Coming soon" @endif>
                {{ $item['label'] }}
            </a>
        @endforeach
    </aside>

    <section class="settings-panel" id="settings-panel">
        <div class="text-muted p-4">Loading...</div>
    </section>
</div>

<div class="modal fade" id="lookupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="lookupForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="lookupModalLabel">Add Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="lookupId">
                    <input type="hidden" id="lookupKey">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" id="lookupName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Code</label>
                        <input type="text" id="lookupCode" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea id="lookupDescription" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="text-danger small" id="lookupFormError"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="departmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="departmentForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="departmentModalLabel">Add Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="departmentId">
                    <div class="mb-3">
                        <label class="form-label">Department Name</label>
                        <input type="text" id="departmentName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Branch</label>
                        <select id="departmentBranch" class="form-select" required>
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->Branch_ID }}">{{ $branch->Branch_Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department Nature</label>
                        <select id="departmentNature" class="form-select" required>
                            <option value="">Select Department Nature</option>
                            @foreach($departmentNatures as $nature)
                                <option value="{{ $nature->id }}">{{ $nature->department_nature }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="text-danger small" id="departmentFormError"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="subdepartmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="subdepartmentForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="subdepartmentModalLabel">Add Subdepartment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="subdepartmentId">
                    <div class="mb-3">
                        <label class="form-label">Subdepartment Name</label>
                        <input type="text" id="subdepartmentName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <select id="subdepartmentDepartment" class="form-select" required>
                            <option value="">Select Department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->Department_ID }}" data-nature="{{ $department->departmentNature->department_nature ?? '' }}">
                                    {{ $department->Department_Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department Nature <small class="text-muted">(inherited)</small></label>
                        <input type="text" id="subdepartmentNaturePreview" class="form-control" disabled placeholder="Select a department first">
                    </div>
                    <div class="text-danger small" id="subdepartmentFormError"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="supplierModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="supplierForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="supplierModalLabel">Add Suppliers</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="supplierId">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Supplier Name *</label>
                            <input type="text" id="supplierName" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Supplier Address *</label>
                            <input type="text" id="supplierAddress" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Postal Address *</label>
                            <input type="text" id="postalAddress" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Contact Person Name *</label>
                            <input type="text" id="contactPersonName" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Contact Person Mobile Number *</label>
                            <input type="text" id="contactPersonMobile" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Contact Person Email *</label>
                            <input type="email" id="contactPersonEmail" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Telephone</label>
                            <input type="text" id="telephone" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fax</label>
                            <input type="text" id="fax" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Physical Address</label>
                            <input type="text" id="physicalAddress" class="form-control">
                        </div>
                    </div>
                    <div class="text-danger small mt-2" id="supplierFormError"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


@section('scripts')
<script src="{{ asset('assets/js/other-settings.js') }}"></script>
@endsection