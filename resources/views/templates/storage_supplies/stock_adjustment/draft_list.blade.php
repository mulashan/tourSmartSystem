@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="settings-panel-head">
    <h2>New Adjustment (Drafts)</h2>
    <a href="{{ route('storage_supplies.stock_adjustment.create') }}" class="btn btn-info text-white"><i class="bi bi-plus-lg"></i> New Adjustment</a>
</div>

<div class="table-responsive">
<table class="table table-hover" data-datatable data-export-name="draft-adjustment-list" data-fixed-columns>
    <thead><tr><th>S/N</th><th>Adjustment No.</th><th>Reason</th><th>Officer</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($items as $i => $adj)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $adj->id }}</td>
                <td>{{ $adj->reason === 'add_stock_balance' ? 'Add Stock Balance' : 'Expired / Dump / Broken' }}</td>
                <td>{{ $adj->officer->name ?? '—' }}</td>
                <td class="text-end">
                    <a href="{{ route('storage_supplies.stock_adjustment.edit', $adj->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <button type="button" class="btn btn-sm btn-warning js-submit-adjustment" data-id="{{ $adj->id }}">Submit for Approval</button>
                    <a href="{{ route('storage_supplies.stock_adjustment.preview', $adj->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
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

        $('.js-submit-adjustment').on('click', function () {
            const id = $(this).data('id');
            Swal.fire({ icon: 'warning', title: 'Submit for approval?', showCancelButton: true, confirmButtonText: 'Yes, submit' }).then(result => {
                if (! result.isConfirmed) return;
                $.post(`/storage-supplies/stock-adjustment/${id}/submit`)
                    .done(() => Swal.fire({ icon: 'success', title: 'Submitted', timer: 1200, showConfirmButton: false }).then(() => location.reload()))
                    .fail(xhr => Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || 'Something went wrong.' }));
            });
        });
    });
});
</script>
@endsection