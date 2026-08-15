@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Fuel Order #{{ $order->id }} — {{ $order->fuelSource->name ?? '—' }}</h2></div>

<div class="mb-3">
    <strong>Status:</strong> {{ ucfirst($order->status) }} &nbsp;|&nbsp;
    <strong>Opened:</strong> {{ $order->opened_at }} by {{ $order->openedBy->name ?? '—' }}
    @if($order->closed_at) &nbsp;|&nbsp; <strong>Closed:</strong> {{ $order->closed_at }} by {{ $order->closedBy->name ?? '—' }} @endif
</div>

<table class="table table-hover" data-datatable data-export-name="open-order-{{ $order->id }}">
    <thead><tr><th>Vehicle</th><th>Driver</th><th>Fuel Type</th><th>Quantity</th><th>Unit Price</th><th>Total</th><th>Odometer</th><th>Recorded By</th><th>Recorded At</th></tr></thead>
    <tbody>
        @forelse($order->items as $item)
            <tr>
                <td>{{ $item->vehicle->registration_no ?? '—' }}</td>
                <td>{{ $item->driver->Employee_Name ?? '—' }}</td>
                <td>{{ $item->fuel_type }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->unit_price, 2) }}</td>
                <td>{{ number_format($item->total_amount, 2) }}</td>
                <td>{{ $item->odometer_reading ?? '—' }}</td>
                <td>{{ $item->recordedBy->name ?? '—' }}</td>
                <td>{{ $item->recorded_at }}</td>
            </tr>
        @empty
            <tr><td colspan="9" class="text-center text-muted">No vehicles served under this order yet.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection