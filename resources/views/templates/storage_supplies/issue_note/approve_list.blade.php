@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Approve Issues</h2></div>

<table class="table table-hover">
    <thead><tr><th>S/N</th><th>Requisition No.</th><th>Store Requesting</th><th>Officer</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($items as $i => $note)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $note->requisition_id }}</td>
                <td>{{ $note->requisition->requestingSubdepartment->Subdepartment_Name ?? '—' }}</td>
                <td>{{ $note->officer->name ?? '—' }}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-warning js-approve-note" data-id="{{ $note->id }}">Approve</button>
                    <a href="{{ route('storage_supplies.issue_note.preview', $note->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted">No Issue Notes pending approval.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="modal fade" id="approveNoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="approveNoteForm">
                <div class="modal-header"><h5 class="modal-title">Approve Issue Note</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" id="approveNoteId">
                    <div class="mb-3"><label class="form-label">Username</label><input type="text" id="approveNoteUsername" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Password</label><input type="password" id="approveNotePassword" class="form-control" required></div>
                    <div class="text-danger small" id="approveNoteFormError"></div>
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
        const modal = new bootstrap.Modal(document.getElementById('approveNoteModal'));

        $('.js-approve-note').on('click', function () {
            $('#approveNoteId').val($(this).data('id'));
            $('#approveNoteForm')[0].reset();
            $('#approveNoteFormError').text('');
            modal.show();
        });

        $('#approveNoteForm').on('submit', function (e) {
            e.preventDefault();
            $.post(`/storage-supplies/issue-note/${$('#approveNoteId').val()}/approve`, {
                username: $('#approveNoteUsername').val(),
                password: $('#approveNotePassword').val(),
            })
                .done(() => Swal.fire({ icon: 'success', title: 'Approved', timer: 1200, showConfirmButton: false }).then(() => location.reload()))
                .fail(xhr => $('#approveNoteFormError').text(xhr.responseJSON?.message || 'Approval failed.'));
        });
    });
});
</script>
@endsection