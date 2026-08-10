@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="settings-panel-head">
    <h2>New GRN — Draft Open Balance / Physical Count Entries</h2>
    <a href="{{ route('storage_supplies.grn_open_balance.create') }}" class="btn btn-info text-white"><i class="bi bi-plus-lg"></i> New </a>
</div>

<div class="table-responsive">
    <table class="table table-hover" style="min-width:900px;" data-datatable data-export-name="Draft-physical-balance-list" data-fixed-columns>
        <thead><tr><th>S/N</th><th>Creation Date</th><th>Description</th><th>Created By</th><th class="text-end">Action</th></tr></thead>
        <tbody>
            @forelse($items as $i => $grn)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $grn->creation_date }}</td>
                    <td>{{ $grn->description }}</td>
                    <td>{{ $grn->createdBy->name ?? '—' }}</td>
                    <td class="text-end">
                        <a href="{{ route('storage_supplies.grn_open_balance.edit', $grn->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        <button type="button" class="btn btn-sm btn-warning js-submit-draft" data-id="{{ $grn->id }}">Submit for Approval</button>
                        <a href="{{ route('storage_supplies.grn_open_balance.preview', $grn->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                    </td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@section('scripts')
<script>
(function whenJQueryReady(fn) {
    if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); }
})(function () {
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        $('.js-submit-draft').on('click', function () {
            const id = $(this).data('id');

            Swal.fire({ icon: 'warning', title: 'Submit for approval?', showCancelButton: true, confirmButtonText: 'Yes, submit' }).then(result => {
                if (! result.isConfirmed) return;

                $.post(`/storage-supplies/grn-open-balance/${id}/submit`)
                    .done(() => Swal.fire({ icon: 'success', title: 'Submitted', timer: 1200, showConfirmButton: false }).then(() => location.reload()))
                    .fail(xhr => Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || 'Something went wrong.' }));
            });
        });
    });
});
</script>
@endsection