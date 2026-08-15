<div class="table-responsive">
    <table class="table table-hover" data-datatable data-export-name="incidents-report">
        <thead><tr><th>S/N</th><th>Type</th><th>Vehicle</th><th>Driver</th><th>Date</th><th>Covered By</th><th>Cost</th></tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r->type === 'accident' ? 'Accident' : 'Road Fine' }}</td>
                    <td>{{ $r->vehicle->registration_no ?? '—' }}</td>
                    <td>{{ $r->driver->Employee_Name ?? '—' }}</td>
                    <td>{{ $r->incident_date }}</td>
                    <td>{{ $r->covered_by ? ucfirst($r->covered_by) : '—' }}</td>
                    <td>{{ number_format($r->actual_cost ?? $r->estimated_cost ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted">No incidents found for these filters.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($rows->isNotEmpty())
    @endif
</div>