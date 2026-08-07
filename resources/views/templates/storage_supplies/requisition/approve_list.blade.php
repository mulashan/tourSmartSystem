@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Approve Requisition</h2></div>

<table class="table table-hover">
    <thead><tr><th>S/N</th><th>Store Issuing</th><th>Officer</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($items as $i => $req)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $req->issuingSubdepartment->Subdepartment_Name ?? '—' }}</td>
                <td>{{ $req->officer->name ?? '—' }}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-warning js-approve-req" data-id="{{ $req->id }}">Approve</button>
                    <a href="{{ route('storage_supplies.requisition.preview', $req->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted">No requisitions pending approval.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="modal fade" id="approveReqModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="approveReqForm">
                <div class="modal-header"><h5 class="modal-title">Approve Requisition</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" id="approveReqId">
                    <div class="mb-3"><label class="form-label">Username</label><input type="text" id="approveReqUsername" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Password</label><input type="password" id="approveReqPassword" class="form-control" required></div>
                    <div class="text-danger small" id="approveReqFormError"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Approve</button></div>
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
        const modal = new bootstrap.Modal(document.getElementById('approveReqModal'));

        $('.js-approve-req').on('click', function () {
            $('#approveReqId').val($(this).data('id'));
            $('#approveReqForm')[0].reset();
            $('#approveReqFormError').text('');
            modal.show();
        });

        $('#approveReqForm').on('submit', function (e) {
            e.preventDefault();
            $.post(`/storage-supplies/requisition/${$('#approveReqId').val()}/approve`, {
                username: $('#approveReqUsername').val(),
                password: $('#approveReqPassword').val(),
            })
                .done(() => Swal.fire({ icon: 'success', title: 'Approved', timer: 1200, showConfirmButton: false }).then(() => location.reload()))
                .fail(xhr => $('#approveReqFormError').text(xhr.responseJSON?.message || 'Approval failed.'));
        });
    });
});
</script>
@endsection