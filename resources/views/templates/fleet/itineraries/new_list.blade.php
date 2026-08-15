@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>New Itinerary</h2><a href="{{ route('fleet.itineraries.create') }}" class="btn btn-info text-white">New Itinerary</a></div>
<div class="table-responsive">
    <table class="table table-hover" data-datatable data-export-name="pending-itineraries">
        <thead><tr><th>Trip No.</th><th>Client(s)</th><th>Destination</th><th>Start</th><th>End</th><th class="text-end">Action</th></tr></thead>
        <tbody>
            @forelse($itineraries as $i)
                <tr>
                    <td>{{ $i->trip_number }}</td><td>{{ $i->clients }}</td><td>{{ $i->destination }}</td><td>{{ $i->start_date }}</td><td>{{ $i->end_date }}</td>
                    <td class="text-end"><a href="{{ route('fleet.itineraries.preview', $i->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a></td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>
@endsection