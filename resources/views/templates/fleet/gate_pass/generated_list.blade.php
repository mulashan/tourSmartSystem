@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Generated Gate Passes</h2></div>

<table class="table table-hover" data-datatable data-export-name="gate-passes-generated">
    <thead><tr><th>Gate Pass No.</th><th>Trip</th><th>Vehicle</th><th>Driver</th><th>Printed</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($issued as $p)
            <tr>
                <td>{{ $p->gate_pass_no }}</td><td>{{ $p->itinerary->trip_number ?? '—' }}</td><td>{{ $p->vehicle->registration_no ?? '—' }}</td><td>{{ $p->driver->Employee_Name ?? '—' }}</td>
                <td>{{ $p->printed_at ? 'Yes' : 'No' }}</td>
                <td class="text-end"><a href="{{ route('fleet.gate_pass.preview', $p->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview / Print</a></td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">No Gate Passes generated yet.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection