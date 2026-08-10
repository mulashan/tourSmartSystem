{{-- partials/requisition_rejection_rate_table.blade.php --}}
<p class="text-muted small">Rejection rate = rejected ÷ (rejected + ordered). Requisitions still pending procurement action aren't counted in the rate.</p>
<div class="table-responsive">
    <table class="table table-hover" data-datatable data-export-name="requisition-rejection-rate-report" data-fixed-columns>
        <thead><tr><th>S/N</th><th>Requesting Store</th><th>Total Approved Requisitions</th><th>Rejected</th><th>Processed (Rejected + Ordered)</th><th>Rejection Rate</th></tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr class="{{ $r['rejection_rate'] >= 30 ? 'table-danger' : ($r['rejection_rate'] >= 15 ? 'table-warning' : '') }}">
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r['store'] }}</td>
                    <td>{{ $r['total'] }}</td>
                    <td>{{ $r['rejected'] }}</td>
                    <td>{{ $r['processed'] }}</td>
                    <td>{{ $r['rejection_rate'] }}%</td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>