@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Fuel Reconciliation</h2></div>

<form class="row g-3 mb-3" method="GET">
    <div class="col-md-6"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control" value="{{ $startDate }}" onchange="this.form.submit()"></div>
    <div class="col-md-6"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-control" value="{{ $endDate }}" onchange="this.form.submit()"></div>
</form>

@foreach($orders as $order)
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between">
            <span>Order #{{ $order->id }} — {{ $order->fuelSource->name ?? '—' }} — {{ ucfirst($order->status) }}</span>
            <span>Opened {{ $order->opened_at }} by {{ $order->openedBy->name ?? '—' }} @if($order->closed_at) | Closed {{ $order->closed_at }} by {{ $order->closedBy->name ?? '—' }} @endif</span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0" data-datatable data-export-name="reconciliation-order-{{ $order->id }}">
                <thead><tr><th>Vehicle</th><th>Fuel Type</th><th>Quantity</th><th>Unit Price</th><th>Total</th><th>Recorded By</th></tr></thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr><td>{{ $item->vehicle->registration_no ?? '—' }}</td><td>{{ $item->fuel_type }}</td><td>{{ $item->quantity }}</td><td>{{ number_format($item->unit_price, 2) }}</td><td>{{ number_format($item->total_amount, 2) }}</td><td>{{ $item->recordedBy->name ?? '—' }}</td></tr>
                    @endforeach
                    <tfoot><tr class="fw-bold"><td colspan="2">Total</td><td>{{ $order->total_quantity }}</td><td></td><td>{{ number_format($order->total_amount, 2) }}</td><td></td></tr></tfoot>
                </tbody>
            </table>
        </div>
    </div>
@endforeach
@if($orders->isEmpty()) @endif
@endsection