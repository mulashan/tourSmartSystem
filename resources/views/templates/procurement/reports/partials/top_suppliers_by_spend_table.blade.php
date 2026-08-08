{{-- partials/top_suppliers_by_spend_table.blade.php --}}
<div class="table-responsive">
    <table class="table table-hover">
        <thead><tr><th>Rank</th><th>Supplier</th><th>Purchase Orders</th><th>Total Spend</th><th>% of Total</th></tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r['supplier'] }}</td>
                    <td>{{ $r['lpo_count'] }}</td>
                    <td>{{ number_format($r['total_spend'], 2) }}</td>
                    <td>{{ $grandTotal > 0 ? round(($r['total_spend'] / $grandTotal) * 100, 1) : 0 }}%</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">No approved purchases in this period.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($rows->isNotEmpty())
        <div class="text-end fw-bold pe-3">Grand Total: {{ number_format($grandTotal, 2) }}</div>
    @endif
</div>