@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Fuel History</h2></div>

@foreach($itineraries as $itinerary)
    @php
        $assignedTotal = $itinerary->fuelAssignments->sum('quantity_assigned');
        $issuedTotal = $itinerary->fuelAssignments->sum('issued_quantity');
    @endphp
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between">
            <span>{{ $itinerary->trip_number }} — {{ $itinerary->vehicle->registration_no ?? '—' }}</span>
            <span>Assigned: {{ $assignedTotal }} &nbsp;|&nbsp; Issued: {{ $issuedTotal }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0" data-datatable data-export-name="fuel-history-{{ $itinerary->id }}">
                <thead><tr><th>Leg</th><th>Source</th><th>Assigned</th><th>Issued</th><th>Status</th><th>Assigned By</th><th>Issued By</th></tr></thead>
                <tbody>
                    @foreach($itinerary->fuelAssignments as $f)
                        <tr>
                            <td>{{ $f->leg ? "Leg {$f->leg->leg_number}: {$f->leg->start_point} → {$f->leg->destination}" : 'Main Trip' }}</td>
                            <td>{{ $f->fuelSource->name ?? '—' }}</td>
                            <td>{{ $f->quantity_assigned }}</td>
                            <td>{{ $f->issued_quantity ?? '—' }}</td>
                            <td><span class="badge bg-{{ $f->status === 'issued' ? 'success' : 'warning text-dark' }}">{{ ucfirst($f->status) }}</span></td>
                            <td>{{ $f->assignedBy->name ?? '—' }}</td>
                            <td>{{ $f->issuedBy->name ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endforeach
@if($itineraries->isEmpty())<div class="text-muted">No fuel history yet.</div>@endif
@endsection