{{-- partials/last_buying_price_table.blade.php --}}
<div class="table-responsive">
    <table class="table table-hover" data-datatable data-export-name="last-buying-report" data-fixed-columns>
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
            @endforelse
        </tbody>
    </table>
</div>