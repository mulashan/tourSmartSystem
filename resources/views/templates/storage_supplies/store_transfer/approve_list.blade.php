@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Pending Approval</h2></div>

<table class="table table-hover">
    <thead><tr><th>S/N</th><th>Transfer To</th><th>Created By</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($items as $i => $t)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $t->toSubdepartment->Subdepartment_Name ?? '—' }}</td>
                <td>{{ $t->createdBy->name ?? '—' }}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-warning js-approve-transfer" data-id="{{ $t->id }}">Approve</button>
                    <button class="btn btn-sm btn-outline-danger js-cancel-transfer" data-id="{{ $t->id }}">Cancel</button>
                    <a href="{{ route('storage_supplies.store_transfer.preview', $t->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted">No transfers pending approval.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="modal fade" id="approveTransferModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="approveTransferForm">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Transfer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="approveTransferId">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" id="approveTransferUsername" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" id="approveTransferPassword" class="form-control" required>
                    </div>
                    <div class="text-danger small" id="approveTransferFormError"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function whenJQueryReady(fn) {
    if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); }
})(function () {
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
        const modal = new bootstrap.Modal(document.getElementById('approveTransferModal'));

        $('.js-approve-transfer').on('click', function () {
            $('#approveTransferId').val($(this).data('id'));
            $('#approveTransferForm')[0].reset();
            $('#approveTransferFormError').text('');
            modal.show();
        });

        $('#approveTransferForm').on('submit', function (e) {
            e.preventDefault();

            $.post(`/storage-supplies/store-transfer/${$('#approveTransferId').val()}/approve`, {
                username: $('#approveTransferUsername').val(),
                password: $('#approveTransferPassword').val(),
            })
                .done(() => {
                    Swal.fire({ icon: 'success', title: 'Approved', timer: 1200, showConfirmButton: false })
                        .then(() => location.reload());
                })
                .fail(xhr => $('#approveTransferFormError').text(xhr.responseJSON?.message || 'Approval failed.'));
        });

        $('.js-cancel-transfer').on('click', function () {
            const id = $(this).data('id');

            Swal.fire({
                icon: 'warning', title: 'Cancel this transfer?', input: 'text', inputLabel: 'Reason',
                showCancelButton: true, confirmButtonText: 'Cancel Transfer',
                inputValidator: v => !v ? 'A reason is required' : undefined,
            }).then(result => {
                if (! result.isConfirmed) return;

                $.post(`/storage-supplies/store-transfer/${id}/cancel`, { reason: result.value })
                    .done(() => Swal.fire({ icon: 'success', title: 'Cancelled', timer: 1200, showConfirmButton: false }).then(() => location.reload()))
                    .fail(xhr => Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || 'Something went wrong.' }));
            });
        });
    });
});
</script>
@endsection