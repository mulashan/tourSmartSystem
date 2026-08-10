@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Approve Return</h2></div>

<div class="table-responsive">
<table class="table table-hover" data-datatable data-export-name="return-approve-list" data-fixed-columns>
    <thead><tr><th>S/N</th><th>Document No.</th><th>Store Receiving</th><th>Posted By</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($items as $i => $r)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $r->id }}</td>
                <td>{{ $r->toSubdepartment->Subdepartment_Name ?? '—' }}</td>
                <td>{{ $r->postedBy->name ?? '—' }}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-warning js-approve-return" data-id="{{ $r->id }}">Approve</button>
                    <a href="{{ route('storage_supplies.return.preview', $r->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                </td>
            </tr>
        @empty
        @endforelse
    </tbody>
</table>

<div class="modal fade" id="approveReturnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="approveReturnForm">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Return</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="approveReturnId">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" id="approveReturnUsername" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" id="approveReturnPassword" class="form-control" required>
                    </div>
                    <div class="text-danger small" id="approveReturnFormError"></div>
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
        const modal = new bootstrap.Modal(document.getElementById('approveReturnModal'));

        $('.js-approve-return').on('click', function () {
            $('#approveReturnId').val($(this).data('id'));
            $('#approveReturnForm')[0].reset();
            $('#approveReturnFormError').text('');
            modal.show();
        });

        $('#approveReturnForm').on('submit', function (e) {
            e.preventDefault();

            $.post(`/storage-supplies/return/${$('#approveReturnId').val()}/approve`, {
                username: $('#approveReturnUsername').val(),
                password: $('#approveReturnPassword').val(),
            })
                .done(() => {
                    Swal.fire({ icon: 'success', title: 'Approved', timer: 1200, showConfirmButton: false })
                        .then(() => location.reload());
                })
                .fail(xhr => $('#approveReturnFormError').text(xhr.responseJSON?.message || 'Approval failed.'));
        });
    });
});
</script>
@endsection