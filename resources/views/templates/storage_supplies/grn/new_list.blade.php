@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>New GRN — Approved Purchase Orders Awaiting Receipt</h2></div>

<section class="section">
    <div class="card">
        <div class="card-body">
            <div class="row mt-3 mb-4">
                <div class="col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search purchase orders...">
                    </div>
                </div>
                <div class="col-lg-8 text-end">
                    <button class="btn btn-outline-success"><i class="bi bi-download"></i> Export</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle" style="min-width:1200px;">
                    <thead class="table-light">
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
                                <td><strong>{{ $lpo->store_requisition_id }}</strong></td>
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
                            <tr><td colspan="10" class="text-center text-muted py-4">No approved Purchase Orders awaiting GRN.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <span class="text-muted">Showing {{ $lpos->count() }} records</span>
            </div>
        </div>
    </div>
</section>
@endsection
