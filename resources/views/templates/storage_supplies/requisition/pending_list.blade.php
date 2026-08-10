@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="settings-panel-head">
    <h2>Pending Requisition</h2>
    <a href="{{ route('storage_supplies.requisition.create') }}" class="btn btn-info text-white"><i class="bi bi-plus-lg"></i> New Requisition</a>
</div>
<div class="table-responsive">
<table class="table table-hover" data-datatable data-export-name="pending-requisitions" data-fixed-columns>
    <thead><tr><th>S/N</th><th>Store Issuing</th><th>Officer</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($items as $i => $req)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $req->issuingSubdepartment->Subdepartment_Name ?? '—' }}</td>
                <td>{{ $req->officer->name ?? '—' }}</td>
                <td class="text-end">
                    <a href="{{ route('storage_supplies.requisition.edit', $req->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <button type="button" class="btn btn-sm btn-warning js-submit-requisition" data-id="{{ $req->id }}">Submit for Approval</button>
                    <a href="{{ route('storage_supplies.requisition.preview', $req->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
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

        $('.js-submit-requisition').on('click', function () {
            const id = $(this).data('id');

            Swal.fire({ icon: 'warning', title: 'Submit for approval?', showCancelButton: true, confirmButtonText: 'Yes, submit' }).then(result => {
                if (! result.isConfirmed) return;

                $.post(`/storage-supplies/requisition/${id}/submit`)
                    .done(() => Swal.fire({ icon: 'success', title: 'Submitted', timer: 1200, showConfirmButton: false }).then(() => location.reload()))
                    .fail(xhr => Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || 'Something went wrong.' }));
            });
        });
    });
});
</script>
@endsection