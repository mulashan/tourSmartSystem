<p class="text-muted small">Based on the average odometer interval between this vehicle's past Maintenance Orders. Needs at least 2 past orders to estimate.</p>
<div class="table-responsive">
    <table class="table table-hover" data-datatable data-export-name="maintenance-due">
        <thead><tr><th>S/N</th><th>Vehicle</th><th>Current Odometer</th><th>Avg. Service Interval</th><th>Next Due (Odometer)</th><th>Remaining (km)</th></tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr class="{{ ! $r['has_history'] ? '' : ($r['remaining'] <= 0 ? 'table-danger' : ($r['remaining'] <= $dueSoonThreshold ? 'table-warning' : '')) }}">
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r['vehicle']->registration_no }}</td>
                    <td>{{ number_format($r['vehicle']->current_odometer) }}</td>
                    <td>{{ $r['has_history'] ? number_format($r['avg_interval']) . ' km' : 'Insufficient history' }}</td>
                    <td>{{ $r['has_history'] ? number_format($r['next_due']) : '—' }}</td>
                    <td>
                        @if(! $r['has_history']) — 
                        @elseif($r['remaining'] <= 0) <span class="text-danger fw-bold">Overdue by {{ number_format(abs($r['remaining'])) }} km</span>
                        @else {{ number_format($r['remaining']) }}
                        @endif
                    </td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>