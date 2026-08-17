<div class="table-responsive">
    <table class="table table-hover" data-datatable data-export-name="driver-performance">
        <thead><tr><th>S/N</th><th>Driver</th><th>Trips Completed</th><th>Total Distance (km)</th><th>Incidents</th><th>Incident Cost</th></tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r['driver']->Employee_Name }}</td>
                    <td>{{ $r['trip_count'] }}</td>
                    <td>{{ number_format($r['distance']) }}</td>
                    <td>{{ $r['incident_count'] }}</td>
                    <td>{{ number_format($r['incident_cost'], 2) }}</td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>