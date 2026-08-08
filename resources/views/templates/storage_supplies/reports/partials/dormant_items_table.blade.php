<p class="text-muted small">Items with no stock movement in the last {{ $withinDays }} days, or with stock but no recorded movement at all.</p>
<div class="table-responsive">
    <table class="table table-hover">
        <thead><tr><th>S/N</th><th>Item Name</th><th>UoM</th><th>Current Balance</th><th>Last Movement</th><th>Days Since</th></tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r['item']->product_name ?? '—' }}</td>
                    <td>{{ $r['item']->unitOfMeasure->name ?? '-' }}</td>
                    <td>{{ $r['balance'] }}</td>
                    <td>{{ $r['last_movement'] ?? 'Never' }}</td>
                    <td>{{ $r['days_since'] ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">No dormant items found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>