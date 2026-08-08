{{-- resources/views/templates/procurement/reports/partials/purchasing_history_table.blade.php --}}
<div class="table-responsive">
    <table class="table table-hover">
        <thead><tr><th>S/N</th><th>Item Name</th><th>Supplier Name</th><th>Purchase Date</th><th>Buying Price</th><th>Quantity</th><th>GRN</th><th>Document Type</th></tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r['item']->product_name ?? '—' }}</td>
                    <td>{{ $r['supplier'] }}</td>
                    <td>{{ $r['purchase_date'] }}</td>
                    <td>{{ number_format($r['buying_price'], 2) }}</td>
                    <td>{{ $r['quantity'] }}</td>
                    <td>{{ $r['grn_no'] }}</td>
                    <td>{{ $r['document_type'] }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted">No purchases in this period.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>