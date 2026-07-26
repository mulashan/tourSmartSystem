@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Local Purchase Orders</h2></div>

<table class="table table-hover">
    <thead><tr><th>LPO #</th><th>Supplier</th><th>Approved By</th><th>Approved At</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($items as $lpo)
            <tr>
                <td>{{ $lpo->local_purchase_order_id }}</td>
                <td>{{ $lpo->supplier->supplier_name ?? '—' }}</td>
                <td>{{ $lpo->approvedBy->name ?? '—' }}</td>
                <td>{{ $lpo->approved_at }}</td>
                <td class="text-end">
                    <a href="{{ route('procurement.local_purchase_order.print', $lpo->local_purchase_order_id) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-printer"></i> Print</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted">No approved Local Purchase Orders.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection