<div class="table-responsive">
    <table class="table table-hover" data-datatable data-export-name="fuel-by-station-trips">
        <thead><tr><th>S/N</th><th>Date</th><th>Vehicle</th><th>Driver</th><th>Petrol Station</th><th>Quantity</th><th>Unit Price</th><th>Total Cost</th></tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r->issued_at }}</td>
                    <td>{{ $r->vehicle->registration_no ?? '—' }}</td>
                    <td>{{ $r->driver->Employee_Name ?? '—' }}</td>
                    <td>{{ $r->fuelSource->name ?? '—' }}</td>
                    <td>{{ $r->issued_quantity }}</td>
                    <td>{{ number_format($r->unit_price, 2) }}</td>
                    <td>{{ number_format($r->issued_quantity * $r->unit_price, 2) }}</td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
    @if($rows->isNotEmpty())
        <div class="text-end fw-bold pe-3">Total Quantity: {{ number_format($totalQty, 2) }} &nbsp;|&nbsp; Total Cost: {{ number_format($totalCost, 2) }}</div>
    @endif
</div>