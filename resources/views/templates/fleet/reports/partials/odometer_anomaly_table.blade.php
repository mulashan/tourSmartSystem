<p class="text-muted small">Flags closed trips whose recorded distance is missing, negative, or under {{ $thresholdPerDay }} km/day for their duration.</p>
<div class="table-responsive">
    <table class="table table-hover" data-datatable data-export-name="odometer-anomaly">
        <thead><tr><th>S/N</th><th>Trip No.</th><th>Vehicle</th><th>Driver</th><th>Duration (days)</th><th>Distance (km)</th><th>km/day</th></tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr class="table-warning">
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r['itinerary']->trip_number }}</td>
                    <td>{{ $r['itinerary']->vehicle->registration_no ?? '—' }}</td>
                    <td>{{ $r['itinerary']->driver->Employee_Name ?? '—' }}</td>
                    <td>{{ $r['days'] }}</td>
                    <td>{{ $r['distance'] ?? 'Missing data' }}</td>
                    <td>{{ $r['per_day'] ?? '—' }}</td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>