<div class="table-responsive">
<table class="table table-hover" data-datatable data-export-name="wastage-loss-report" data-fixed-columns>
        <thead><tr><th>S/N</th><th>Item Name</th><th>UoM</th><th>Quantity Lost</th><th>Estimated Value</th></tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r['item']->product_name ?? '—' }}</td>
                    <td>{{ $r['item']->unitOfMeasure->name ?? '-' }}</td>
                    <td>{{ $r['quantity'] }}</td>
                    <td>{{ number_format($r['value'], 2) }}</td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
    @if($rows->isNotEmpty())
        <div class="text-end fw-bold pe-3">Total Estimated Loss: {{ number_format($totalValue, 2) }}</div>
    @endif
</div>