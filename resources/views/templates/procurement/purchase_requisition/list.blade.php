@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Purchase Requisition (Drafts)</h2></div>

<section class="section">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" data-datatable data-export-name="purchase-requisition-list" data-fixed-columns>
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
                                    <button type="button" class="btn btn-sm btn-outline-danger js-cancel-lpo" data-id="{{ $lpo->local_purchase_order_id }}">Cancel LPO</button>
                                </td>
                            </tr>
                        @empty
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
$('.js-cancel-lpo').on('click', function () {
    const id = $(this).data('id');

    Swal.fire({
        icon: 'warning',
        title: 'Cancel this Purchase Order?',
        input: 'text',
        inputLabel: 'Reason for cancellation',
        showCancelButton: true,
        confirmButtonText: 'Cancel LPO',
        inputValidator: v => !v ? 'A reason is required' : undefined,
    }).then(result => {
        if (! result.isConfirmed) return;

        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
        $.post(`/procurement/purchase-requisition/${id}/cancel`, { reason: result.value })
            .done(() => Swal.fire({ icon: 'success', title: 'Cancelled', timer: 1200, showConfirmButton: false }).then(() => location.reload()))
            .fail(xhr => Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || 'Something went wrong.' }));
    });
});
</script>
@endsection
