@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="settings-panel-head">
    <h2>Draft (New Transfer)</h2>
    <a href="{{ route('storage_supplies.store_transfer.create') }}" class="btn btn-info text-white"><i class="bi bi-plus-lg"></i> New Transfer</a>
</div>

<table class="table table-hover">
    <thead><tr><th>S/N</th><th>Transfer To</th><th>Created By</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($items as $i => $t)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $t->toSubdepartment->Subdepartment_Name ?? '—' }}</td>
                <td>{{ $t->createdBy->name ?? '—' }}</td>
                <td class="text-end">
                    <a href="{{ route('storage_supplies.store_transfer.edit', $t->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <button type="button" class="btn btn-sm btn-warning js-submit-transfer" data-id="{{ $t->id }}">Submit for Approval</button>
                    <button type="button" class="btn btn-sm btn-outline-danger js-cancel-transfer" data-id="{{ $t->id }}">Cancel</button>
                    <a href="{{ route('storage_supplies.store_transfer.preview', $t->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted">No draft transfers.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection

@section('scripts')
<script>
(function whenJQueryReady(fn) {
    if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); }
})(function () {
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        $('.js-submit-transfer').on('click', function () {
            const id = $(this).data('id');
            Swal.fire({ icon: 'warning', title: 'Submit for approval?', showCancelButton: true, confirmButtonText: 'Yes, submit' }).then(result => {
                if (! result.isConfirmed) return;
                $.post(`/storage-supplies/store-transfer/${id}/submit`)
                    .done(() => Swal.fire({ icon: 'success', title: 'Submitted', timer: 1200, showConfirmButton: false }).then(() => location.reload()))
                    .fail(xhr => Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || 'Something went wrong.' }));
            });
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