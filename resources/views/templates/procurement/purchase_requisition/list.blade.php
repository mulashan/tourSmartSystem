@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Purchase Requisition (Drafts)</h2></div>

<section class="section">
    <div class="card">
        <div class="card-body">
            <div class="row mt-3 mb-4">
                <div class="col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search purchase requisitions...">
                    </div>
                </div>
                <div class="col-lg-8 text-end">
                    <button class="btn btn-outline-success"><i class="bi bi-download"></i> Export</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr><th>LPO #</th><th>Store Requisition</th><th>Supplier</th><th>Created By</th><th class="text-end">Action</th></tr>
                    </thead>
                    <tbody>
                        @forelse($items as $lpo)
                            <tr>
                                <td><strong>{{ $lpo->local_purchase_order_id }}</strong></td>
                                <td>{{ $lpo->storeRequisition->subdepartment->Subdepartment_Name ?? '—' }} (#{{ $lpo->store_requisition_id }})</td>
                                <td>{{ $lpo->supplier->supplier_name ?? '—' }}</td>
                                <td>{{ $lpo->createdBy->name ?? '—' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('procurement.purchase_requisition.edit', $lpo->local_purchase_order_id) }}" class="btn btn-sm btn-info text-white">Continue Editing</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No draft Purchase Orders.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <span class="text-muted">Showing {{ $items->count() }} records</span>
            </div>
        </div>
    </div>
</section>
@endsection
