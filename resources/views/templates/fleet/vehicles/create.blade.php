@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<form action="{{ route('fleet.vehicles.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="card mb-3">
        <div class="card-header">Vehicle Details</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Registration No. *</label><input type="text" name="registration_no" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label">Vehicle Type</label><input type="text" name="vehicle_type" class="form-control" placeholder="e.g. Land Cruiser, Bus"></div>
                <div class="col-md-4"><label class="form-label">Make</label><input type="text" name="make" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">Model</label><input type="text" name="model" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">Year</label><input type="number" name="year" class="form-control" min="1950" max="{{ now()->year + 1 }}"></div>
                <div class="col-md-4"><label class="form-label">Color</label><input type="text" name="color" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">Chassis No.</label><input type="text" name="chassis_no" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">Engine No.</label><input type="text" name="engine_no" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">Seating Capacity</label><input type="number" name="seating_capacity" class="form-control" min="1"></div>
                <div class="col-md-4">
                    <label class="form-label">Fuel Type</label>
                    <select name="fuel_type" class="form-select">
                        <option value="">Select...</option>
                        <option value="Petrol">Petrol</option>
                        <option value="Diesel">Diesel</option>
                        <option value="Electric">Electric</option>
                        <option value="Hybrid">Hybrid</option>
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label">Current Odometer</label><input type="number" name="current_odometer" class="form-control" min="0" value="0"></div>
                <div class="col-md-4">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="available">Available</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="internal_workshop">Internal Workshop</option>
                        <option value="external_workshop">External Workshop</option>
                        <option value="on_trip">On Trip</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Ownership & Location</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Ownership *</label>
                    <select name="ownership_type_id" id="ownershipTypeSelect" class="form-select" required>
                        <option value="">Select...</option>
                        @foreach($ownershipTypes as $o)<option value="{{ $o->id }}" data-name="{{ strtolower($o->name) }}">{{ $o->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Current Station/Location</label>
                    <select name="current_location_id" class="form-select">
                        <option value="">Select...</option>
                        @foreach($locations as $l)<option value="{{ $l->id }}">{{ $l->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Assigned Driver (Optional)</label>
                    <select name="assigned_driver_employee_id" class="form-select">
                        <option value="">None</option>
                        @foreach($drivers as $d)<option value="{{ $d->Employee_ID }}">{{ $d->Employee_Name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label">Owner</label><input type="text" name="owner" class="form-control"></div>
                <div class="col-md-4">
                    <div class="form-check mt-4">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive" checked>
                        <label class="form-check-label" for="isActive">Active</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3 d-none" id="rentalSection">
        <div class="card-header">Rental Agreement</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Start Date</label><input type="date" name="rental_start_date" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">End Date</label><input type="date" name="rental_end_date" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">Contact Information</label><input type="text" name="rental_contact_info" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Agreement Document</label><input type="file" name="rental_agreement_document" class="form-control"></div>
            </div>
        </div>
    </div>

    <div class="text-end">
        <a href="{{ route('fleet.vehicles.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-info text-white">Save Vehicle</button>
    </div>
</form>
@endsection

@section('scripts')
<script>
(function whenJQueryReady(fn) {
    if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); }
})(function () {
    $(function () {
        function toggleRentalSection() {
            const selected = $('#ownershipTypeSelect option:selected').data('name') || '';
            $('#rentalSection').toggleClass('d-none', ! selected.includes('rent'));
        }
        $('#ownershipTypeSelect').on('change', toggleRentalSection);
        toggleRentalSection();
    });
});
</script>
@endsection