@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Approve Adjustments</h2></div>

<div class="table-responsive">
<table class="table table-hover" data-datatable data-export-name="approve-adjustment-list" data-fixed-columns>
    <thead><tr><th>S/N</th><th>Adjustment No.</th><th>Reason</th><th>Officer</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($items as $i => $adj)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $adj->id }}</td>
                <td>{{ $adj->reason === 'add_stock_balance' ? 'Add Stock Balance' : 'Expired / Dump / Broken' }}</td>
                <td>{{ $adj->officer->name ?? '—' }}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-warning js-approve-adjustment" data-id="{{ $adj->id }}">Approve</button>
                    <a href="{{ route('storage_supplies.stock_adjustment.preview', $adj->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                </td>
            </tr>
        @empty
        @endforelse
    </tbody>
</table>

<div class="modal fade" id="approveAdjustmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="approveAdjustmentForm">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Adjustment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="approveAdjustmentId">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" id="approveAdjustmentUsername" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" id="approveAdjustmentPassword" class="form-control" required>
                    </div>
                    <div class="text-danger small" id="approveAdjustmentFormError"></div>
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
        const modal = new bootstrap.Modal(document.getElementById('approveAdjustmentModal'));

        $('.js-approve-adjustment').on('click', function () {
            $('#approveAdjustmentId').val($(this).data('id'));
            $('#approveAdjustmentForm')[0].reset();
            $('#approveAdjustmentFormError').text('');
            modal.show();
        });

        $('#approveAdjustmentForm').on('submit', function (e) {
            e.preventDefault();

            $.post(`/storage-supplies/stock-adjustment/${$('#approveAdjustmentId').val()}/approve`, {
                username: $('#approveAdjustmentUsername').val(),
                password: $('#approveAdjustmentPassword').val(),
            })
                .done(() => {
                    Swal.fire({ icon: 'success', title: 'Approved', timer: 1200, showConfirmButton: false })
                        .then(() => location.reload());
                })
                .fail(xhr => $('#approveAdjustmentFormError').text(xhr.responseJSON?.message || 'Approval failed.'));
        });
    });
});
</script>
@endsection