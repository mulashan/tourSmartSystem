<div class="table-responsive">
    <table class="table table-hover" style="min-width:1200px;">
        <thead>
            <tr>
                <th>S/N</th><th>Item Name</th><th>Item Code</th><th>UoM</th><th>Open Balance</th>
                <th>Received</th><th>Dispensed</th><th>Returned</th><th>Issued</th>
                <th>Adjustment +</th><th>Adjustment -</th><th>Stock Value</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r['item']->product_name }}</td>
                    <td>{{ trim(($r['item']->product_code_prefix ?? '') . ' ' . ($r['item']->product_code ?? '')) ?: '—' }}</td>
                    <td>{{ $r['item']->unitOfMeasure->name ?? '-' }}</td>
                    <td>{{ $r['open_balance'] }}</td>
                    <td>{{ $r['received'] }}</td>
                    <td>{{ $r['dispensed'] }}</td>
                    <td>{{ $r['returned'] }}</td>
                    <td>{{ $r['issued'] }}</td>
                    <td>{{ $r['adjustment_plus'] }}</td>
                    <td>{{ $r['adjustment_minus'] }}</td>
                    <td>{{ number_format($r['stock_value'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="12" class="text-center text-muted">No items found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>