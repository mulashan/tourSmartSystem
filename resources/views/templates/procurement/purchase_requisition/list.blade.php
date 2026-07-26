@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Purchase Requisition (Drafts)</h2></div>

<table class="table table-hover">
    <thead>
        <tr><th>LPO #</th><th>Store Requisition</th><th>Supplier</th><th>Created By</th><th class="text-end">Action</th></tr>
    </thead>
    <tbody>
        @forelse($items as $lpo)
            <tr>
                <td>{{ $lpo->local_purchase_order_id }}</td>
                <td>{{ $lpo->storeRequisition->subdepartment->Subdepartment_Name ?? '—' }} (#{{ $lpo->store_requisition_id }})</td>
                <td>{{ $lpo->supplier->supplier_name ?? '—' }}</td>
                <td>{{ $lpo->createdBy->name ?? '—' }}</td>
                <td class="text-end">
                    <a href="{{ route('procurement.purchase_requisition.edit', $lpo->local_purchase_order_id) }}" class="btn btn-sm btn-info text-white">Continue Editing</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted">No draft Purchase Orders.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection