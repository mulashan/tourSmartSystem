<p class="text-muted small">Downtime is measured from Maintenance Order open→complete windows only — the system does not yet separately track Internal vs External Workshop as distinct statuses, and does not log arbitrary status-change history.</p>
<div class="table-responsive">
    <table class="table table-hover" data-datatable data-export-name="vehicle-downtime">
        <thead><tr><th>S/N</th><th>Vehicle</th><th>Period Days</th><th>Downtime Days</th><th>Available Days</th><th>Availability %</th></tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr class="{{ $r['availability_pct'] < 80 ? 'table-warning' : '' }}">
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r['vehicle']->registration_no }}</td>
                    <td>{{ $periodDays }}</td>
                    <td>{{ $r['downtime_days'] }}</td>
                    <td>{{ $r['available_days'] }}</td>
                    <td>{{ $r['availability_pct'] }}%</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">No vehicles found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>