@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Local Purchase Orders</h2></div>

<section class="section">
    <div class="card">
        <div class="card-body">
            <div class="row mt-3 mb-4">
                <div class="col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search local purchase orders...">
                    </div>
                </div>
                <div class="col-lg-8 text-end">
                    <button class="btn btn-outline-success"><i class="bi bi-download"></i> Export</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light"><tr><th>LPO #</th><th>Supplier</th><th>Approved By</th><th>Approved At</th><th class="text-end">Action</th></tr></thead>
                    <tbody>
                        @forelse($items as $lpo)
                            <tr>
                                <td><strong>{{ $lpo->local_purchase_order_id }}</strong></td>
                                <td>{{ $lpo->supplier->supplier_name ?? '—' }}</td>
                                <td>{{ $lpo->approvedBy->name ?? '—' }}</td>
                                <td>{{ $lpo->approved_at }}</td>
                                <td class="text-end">
                                    <a href="{{ route('procurement.local_purchase_order.print', $lpo->local_purchase_order_id) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-printer"></i> Print</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No approved Local Purchase Orders.</td></tr>
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
