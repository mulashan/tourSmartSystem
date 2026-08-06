@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="settings-panel-head">
    <h2>New Return (Drafts)</h2>
    <a href="{{ route('storage_supplies.return_outward.create') }}" class="btn btn-info text-white"><i class="bi bi-plus-lg"></i> New Return</a>
</div>

<table class="table table-hover">
    <thead><tr><th>S/N</th><th>Document No.</th><th>Description</th><th>Supplier</th><th>Posted By</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($items as $i => $r)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $r->id }}</td>
                <td>{{ $r->description }}</td>
                <td>{{ $r->supplier->supplier_name ?? '—' }}</td>
                <td>{{ $r->postedBy->name ?? '—' }}</td>
                <td class="text-end">
                    <a href="{{ route('storage_supplies.return_outward.edit', $r->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <button type="button" class="btn btn-sm btn-warning js-submit-return" data-id="{{ $r->id }}">Send for Approval</button>
                    <a href="{{ route('storage_supplies.return_outward.preview', $r->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">No draft returns.</td></tr>
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

        $('.js-submit-return').on('click', function () {
            const id = $(this).data('id');

            Swal.fire({ icon: 'warning', title: 'Send for approval?', showCancelButton: true, confirmButtonText: 'Yes, send' }).then(result => {
                if (! result.isConfirmed) return;

                $.post(`/storage-supplies/return-outward/${id}/submit`)
                    .done(() => Swal.fire({ icon: 'success', title: 'Sent for approval', timer: 1200, showConfirmButton: false }).then(() => location.reload()))
                    .fail(xhr => Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || 'Something went wrong.' }));
            });
        });
    });
});
</script>
@endsection