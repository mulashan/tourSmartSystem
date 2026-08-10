{{-- partials/top_suppliers_by_spend_table.blade.php --}}
<div class="table-responsive">
    <table class="table table-hover" data-datatable data-export-name="top-supplier-spend" data-fixed-columns>
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
            @endforelse
        </tbody>
    </table>
    @if($rows->isNotEmpty())
        <div class="text-end fw-bold pe-3">Grand Total: {{ number_format($grandTotal, 2) }}</div>
    @endif
</div>