@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<form action="{{ route('fleet.vehicles.update', $vehicle->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="card mb-3">
        <div class="card-header">Edit Vehicle — {{ $vehicle->vehicle_code }}</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Registration No. *</label><input type="text" name="registration_no" class="form-control" value="{{ $vehicle->registration_no }}" required></div>
                <div class="col-md-4"><label class="form-label">Vehicle Type</label><input type="text" name="vehicle_type" class="form-control" value="{{ $vehicle->vehicle_type }}"></div>
                <div class="col-md-4"><label class="form-label">Make</label><input type="text" name="make" class="form-control" value="{{ $vehicle->make }}"></div>
                <div class="col-md-4"><label class="form-label">Model</label><input type="text" name="model" class="form-control" value="{{ $vehicle->model }}"></div>
                <div class="col-md-4"><label class="form-label">Year</label><input type="number" name="year" class="form-control" value="{{ $vehicle->year }}"></div>
                <div class="col-md-4"><label class="form-label">Color</label><input type="text" name="color" class="form-control" value="{{ $vehicle->color }}"></div>
                <div class="col-md-4"><label class="form-label">Chassis No.</label><input type="text" name="chassis_no" class="form-control" value="{{ $vehicle->chassis_no }}"></div>
                <div class="col-md-4"><label class="form-label">Engine No.</label><input type="text" name="engine_no" class="form-control" value="{{ $vehicle->engine_no }}"></div>
                <div class="col-md-4"><label class="form-label">Seating Capacity</label><input type="number" name="seating_capacity" class="form-control" value="{{ $vehicle->seating_capacity }}"></div>
                <div class="col-md-4">
                    <label class="form-label">Fuel Type</label>
                    <select name="fuel_type" class="form-select">
                        @foreach(['Petrol', 'Diesel', 'Electric', 'Hybrid'] as $ft)
                            <option value="{{ $ft }}" {{ $vehicle->fuel_type === $ft ? 'selected' : '' }}>{{ $ft }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label">Current Odometer</label><input type="number" name="current_odometer" class="form-control" value="{{ $vehicle->current_odometer }}"></div>
                <div class="col-md-4">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-select" required>
                        @foreach(['available' => 'Available', 'maintenance' => 'Maintenance', 'internal_workshop' => 'Internal Workshop', 'external_workshop' => 'External Workshop', 'on_trip' => 'On Trip', 'inactive' => 'Inactive'] as $val => $label)
                            <option value="{{ $val }}" {{ $vehicle->status === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ownership *</label>
                    <select name="ownership_type_id" class="form-select" required>
                        @foreach($ownershipTypes as $o)<option value="{{ $o->id }}" {{ $vehicle->ownership_type_id == $o->id ? 'selected' : '' }}>{{ $o->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Current Location</label>
                    <select name="current_location_id" class="form-select">
                        <option value="">Select...</option>
                        @foreach($locations as $l)<option value="{{ $l->id }}" {{ $vehicle->current_location_id == $l->id ? 'selected' : '' }}>{{ $l->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Assigned Driver</label>
                    <select name="assigned_driver_employee_id" class="form-select">
                        <option value="">None</option>
                        @foreach($drivers as $d)
                            <option value="{{ $d->Employee_ID }}" {{ $vehicle->assigned_driver_employee_id == $d->Employee_ID ? 'selected' : '' }}>{{ $d->Employee_Name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="form-check mt-4">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive" {{ $vehicle->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="isActive">Active</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-end">
        <a href="{{ route('fleet.vehicles.show', $vehicle->id) }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-info text-white">Save Changes</button>
    </div>
</form>
@endsection