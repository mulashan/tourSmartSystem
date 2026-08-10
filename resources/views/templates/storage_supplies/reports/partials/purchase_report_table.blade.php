<div class="card"><div class="card-body">
<div class="table-responsive">
<table class="table table-hover" data-datatable data-export-name="purchase-report" data-fixed-columns>
        <thead><tr><th>S/N</th><th>Item Name</th><th>Item Folio No.</th><th>UoM</th><th>Quantity</th><th>Amount</th><th>GRN No.</th><th>GRN Date</th><th>Supplier</th></tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r['item']->product_name ?? '—' }}</td>
                    <td>{{ trim(($r['item']->product_code_prefix ?? '') . ' ' . ($r['item']->product_code ?? '')) ?: '—' }}</td>
                    <td>{{ $r['item']->unitOfMeasure->name ?? '-' }}</td>
                    <td>{{ $r['quantity'] }}</td>
                    <td>{{ number_format($r['amount'], 2) }}</td>
                    <td>{{ $r['grn_no'] }}</td>
                    <td>{{ $r['grn_date'] }}</td>
                    <td>{{ $r['supplier'] }}</td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div></div></div>