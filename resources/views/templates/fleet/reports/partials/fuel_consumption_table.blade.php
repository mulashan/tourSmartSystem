<div class="table-responsive">
    <table class="table table-hover" data-datatable data-export-name="fuel-consumption">
        <thead><tr><th>S/N</th><th>Vehicle</th><th>Total Quantity</th><th>Total Amount</th></tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r['vehicle'] }}</td>
                    <td>{{ number_format($r['total_quantity'], 2) }}</td>
                    <td>{{ number_format($r['total_amount'], 2) }}</td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>