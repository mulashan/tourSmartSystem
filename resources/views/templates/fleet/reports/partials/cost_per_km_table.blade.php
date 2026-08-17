<div class="table-responsive">
    <table class="table table-hover" data-datatable data-export-name="cost-per-km">
        <thead><tr><th>S/N</th><th>Vehicle</th><th>Distance (km)</th><th>Total Fuel Cost</th><th>Cost per km</th></tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r['vehicle']->registration_no }}</td>
                    <td>{{ number_format($r['distance']) }}</td>
                    <td>{{ number_format($r['total_cost'], 2) }}</td>
                    <td>{{ $r['cost_per_km'] !== null ? number_format($r['cost_per_km'], 2) : '—' }}</td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>