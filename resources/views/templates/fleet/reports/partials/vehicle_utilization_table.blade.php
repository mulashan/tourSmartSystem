<div class="table-responsive"><table class="table table-hover" data-datatable data-export-name="vehicle-utilization">
    <thead><tr><th>Vehicle</th><th>Trips</th><th>Days Used</th></tr></thead>
    <tbody>
        @forelse($rows as $r)
            <tr><td>{{ $r['vehicle']->registration_no }}</td><td>{{ $r['trip_count'] }}</td><td>{{ $r['days_used'] }}</td></tr>
        @empty
        @endforelse
    </tbody>
</table></div>