@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Approve GRN</h2></div>

<section class="section">
    <div class="card">
        <div class="card-body">
            <div class="row mt-3 mb-4">
                <div class="col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search GRNs...">
                    </div>
                </div>
                <div class="col-lg-8 text-end">
                    <button class="btn btn-outline-success"><i class="bi bi-download"></i> Export</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle" style="min-width:1200px;">
                    <thead class="table-light">
                        <tr>
                            <th>S/N</th><th>LPO No.</th><th>Delivery Date</th><th>Store Requesting</th>
                            <th>Supplier</th><th>Delivery Note Number</th><th>Invoice Number</th><th>Created By</th><th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $i => $grn)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td><strong>{{ $grn->local_purchase_order_id }}</strong></td>
                                <td>{{ $grn->Delivery_Date }}</td>
                                <td>{{ $grn->subdepartment->Subdepartment_Name ?? '—' }}</td>
                                <td>{{ $grn->supplier->supplier_name ?? '—' }}</td>
                                <td>{{ $grn->Delivery_Note_Number }}</td>
                                <td>{{ $grn->Invoice_Number }}</td>
                                <td>{{ $grn->createdBy->name ?? '—' }}</td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-warning js-approve-grn" data-id="{{ $grn->Grn_Purchase_Order_ID }}">Approve</button>
                                    <a href="{{ route('storage_supplies.grn.preview', $grn->Grn_Purchase_Order_ID) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4">No GRNs pending approval.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <span class="text-muted">Showing {{ $items->count() }} records</span>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="approveGrnModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
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
            $.post(`/storage-supplies/grn/${$('#approveGrnId').val()}/approve`, {
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
