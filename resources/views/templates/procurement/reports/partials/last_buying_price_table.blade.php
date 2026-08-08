{{-- partials/last_buying_price_table.blade.php --}}
<div class="table-responsive">
    <table class="table table-hover">
        <thead><tr><th>S/N</th><th>Item Name</th><th>UoM</th><th>Last Buying Price</th><th>Supplier</th><th>Last Purchase Date</th><th>LPO No.</th></tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r['item']->product_name }}</td>
                    <td>{{ $r['item']->unitOfMeasure->name ?? '-' }}</td>
                    <td>{{ number_format($r['last_price'], 2) }}</td>
                    <td>{{ $r['supplier'] ?? '—' }}</td>
                    <td>{{ $r['purchase_date']?->format('Y-m-d') }}</td>
                    <td>{{ $r['lpo_no'] }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted">No purchase history found for these filters.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>