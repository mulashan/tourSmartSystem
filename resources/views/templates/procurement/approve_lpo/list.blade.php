@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Approve Local Purchase Orders</h2></div>

<table class="table table-hover">
    <thead><tr><th>LPO #</th><th>Store Requisition</th><th>Supplier</th><th>Created By</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($items as $lpo)
            <tr>
                <td>{{ $lpo->local_purchase_order_id }}</td>
                <td>{{ $lpo->storeRequisition->subdepartment->Subdepartment_Name ?? '—' }} (#{{ $lpo->store_requisition_id }})</td>
                <td>{{ $lpo->supplier->supplier_name ?? '—' }}</td>
                <td>{{ $lpo->createdBy->name ?? '—' }}</td>
                <td class="text-end"><button class="btn btn-sm btn-warning js-approve" data-id="{{ $lpo->local_purchase_order_id }}">Approve</button></td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted">No Purchase Orders pending approval.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="modal fade" id="approveLpoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="approveLpoForm">
                <div class="modal-header"><h5 class="modal-title">Approve Purchase Order</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" id="approveLpoId">
                    <div class="mb-3"><label class="form-label">Username</label><input type="text" id="approveLpoUsername" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Password</label><input type="password" id="approveLpoPassword" class="form-control" required></div>
                    <div class="text-danger small" id="approveLpoFormError"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Approve</button></div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    const modal = new bootstrap.Modal(document.getElementById('approveLpoModal'));

    $('.js-approve').on('click', function () {
        $('#approveLpoId').val($(this).data('id'));
        $('#approveLpoForm')[0].reset();
        $('#approveLpoFormError').text('');
        modal.show();
    });

    $('#approveLpoForm').on('submit', function (e) {
        e.preventDefault();
        $.post(`/procurement/approve-lpo/${$('#approveLpoId').val()}/approve`, {
            username: $('#approveLpoUsername').val(),
            password: $('#approveLpoPassword').val(),
        })
            .done(() => Swal.fire({ icon: 'success', title: 'Approved', timer: 1200, showConfirmButton: false }).then(() => location.reload()))
            .fail(xhr => $('#approveLpoFormError').text(xhr.responseJSON?.message || 'Approval failed.'));
    });
});
</script>
@endsection