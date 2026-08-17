<div class="table-responsive">
    <table class="table table-hover" data-datatable data-export-name="fuel-by-station-open-orders">
        <thead><tr><th>Order #</th><th>Petrol Station</th><th>Status</th><th>Opened</th><th>Closed</th><th>Total Quantity</th><th>Total Amount</th><th class="text-end">Action</th></tr></thead>
        <tbody>
            @forelse($orders as $o)
                <tr>
                    <td>{{ $o->id }}</td>
                    <td>{{ $o->fuelSource->name ?? '—' }}</td>
                    <td>{{ ucfirst($o->status) }}</td>
                    <td>{{ $o->opened_at }}</td>
                    <td>{{ $o->closed_at ?? '—' }}</td>
                    <td>{{ $o->total_quantity }}</td>
                    <td>{{ number_format($o->total_amount, 2) }}</td>
                    <td class="text-end"><a href="{{ route('fleet.fuel.open_order_show', $o->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a></td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>