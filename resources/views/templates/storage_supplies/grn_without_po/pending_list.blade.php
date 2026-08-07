@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Approve GRN — Without Purchase Order</h2></div>

<div class="table-responsive">
    <table class="table table-hover" style="min-width:1100px;">
        <thead>
            <tr>
                <th>S/N</th><th>Delivery Date</th><th>Store Requesting</th><th>Supplier</th>
                <th>Delivery Note Number</th><th>Invoice Number</th><th>Created By</th><th class="text-end">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $i => $grn)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $grn->delivery_date }}</td>
                    <td>{{ $grn->subdepartment->Subdepartment_Name ?? '—' }}</td>
                    <td>{{ $grn->supplier->supplier_name ?? '—' }}</td>
                    <td>{{ $grn->delivery_note_number }}</td>
                    <td>{{ $grn->invoice_number }}</td>
                    <td>{{ $grn->createdBy->name ?? '—' }}</td>
                    <td class="text-end">
                        <a href="{{ route('storage_supplies.grn_without_po.edit', $grn->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        <button class="btn btn-sm btn-warning js-approve-grn" data-id="{{ $grn->id }}">Approve</button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted">No GRNs pending approval.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="modal fade" id="approveGrnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="approveGrnForm">
                <div class="modal-header"><h5 class="modal-title">Approve GRN</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" id="approveGrnId">
                    <div class="mb-3"><label class="form-label">Username</label><input type="text" id="approveGrnUsername" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Password</label><input type="password" id="approveGrnPassword" class="form-control" required></div>
                    <div class="text-danger small" id="approveGrnFormError"></div>
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
        const modal = new bootstrap.Modal(document.getElementById('approveGrnModal'));

        $('.js-approve-grn').on('click', function () {
            $('#approveGrnId').val($(this).data('id'));
            $('#approveGrnForm')[0].reset();
            $('#approveGrnFormError').text('');
            modal.show();
        });

        $('#approveGrnForm').on('submit', function (e) {
            e.preventDefault();
            $.post(`/storage-supplies/grn-without-po/${$('#approveGrnId').val()}/approve`, {
                username: $('#approveGrnUsername').val(),
                password: $('#approveGrnPassword').val(),
            })
                .done(() => Swal.fire({ icon: 'success', title: 'GRN approved', timer: 1200, showConfirmButton: false }).then(() => location.reload()))
                .fail(xhr => $('#approveGrnFormError').text(xhr.responseJSON?.message || 'Approval failed.'));
        });
    });
});
</script>
@endsection