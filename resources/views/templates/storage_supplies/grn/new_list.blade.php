@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>New GRN — Approved Purchase Orders Awaiting Receipt</h2></div>

<div class="table-responsive">
    <table class="table table-hover" style="min-width:1200px;">
        <thead>
            <tr>
                <th>S/N</th><th>Store Order Requisition No.</th><th>Purchase Requisition No.</th>
                <th>Local Purchase Order No.</th><th>Created Date</th><th>Store Requesting</th>
                <th>Supplier</th><th>Purchase Description</th><th>Created By</th><th class="text-end">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lpos as $i => $lpo)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $lpo->store_requisition_id }}</td>
                    <td>{{ $lpo->local_purchase_order_id }}</td>
                    <td>{{ $lpo->local_purchase_order_id }}</td>
                    <td>{{ $lpo->created_at->format('Y-m-d H:i:s') }}</td>
                    <td>{{ $lpo->storeRequisition->subdepartment->Subdepartment_Name ?? '—' }}</td>
                    <td>{{ $lpo->supplier->supplier_name ?? '—' }}</td>
                    <td>{{ $lpo->requisition_description }}</td>
                    <td>{{ $lpo->createdBy->name ?? '—' }}</td>
                    <td class="text-end">
                        <a href="{{ route('storage_supplies.grn.create', $lpo->local_purchase_order_id) }}" class="btn btn-sm btn-info text-white">Process</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-center text-muted">No approved Purchase Orders awaiting GRN.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection