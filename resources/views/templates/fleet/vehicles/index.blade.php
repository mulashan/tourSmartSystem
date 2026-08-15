@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="settings-panel-head">
    <h2>Vehicles</h2>
    <a href="{{ route('fleet.vehicles.create') }}" class="btn btn-info text-white"><i class="bi bi-plus-lg"></i> Add New Vehicle</a>
</div>

<div class="table-responsive">
    <table class="table table-hover" data-datatable data-export-name="vehicles">
        <thead>
            <tr>
                <th>Vehicle Code</th><th>Registration No.</th><th>Make/Model</th><th>Status</th>
                <th>Current Location</th><th>Assigned Driver</th><th class="text-end">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vehicles as $v)
                <tr>
                    <td>{{ $v->vehicle_code }}</td>
                    <td>{{ $v->registration_no }}</td>
                    <td>{{ trim("{$v->make} {$v->model}") ?: '—' }}</td>
                    <td><span class="badge bg-{{ $v->status === 'available' ? 'success' : ($v->status === 'on_trip' ? 'info' : 'secondary') }}">{{ ucwords(str_replace('_', ' ', $v->status)) }}</span></td>
                    <td>{{ $v->currentLocation->name ?? '—' }}</td>
                    <td>{{ $v->assignedDriver->Employee_Name ?? ($v->assignedDriver ? '' : '—') }}</td>
                    <td class="text-end"><a href="{{ route('fleet.vehicles.show', $v->id) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>
@endsection