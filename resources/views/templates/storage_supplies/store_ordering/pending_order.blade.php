@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="settings-panel-head"><h2>Pending Orders</h2></div>

<section class="section">
    <div class="card">
        <div class="card-body">
            <div class="row mt-3 mb-4">
                <div class="col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search pending orders...">
                    </div>
                </div>
                <div class="col-lg-8 text-end">
                    <button class="btn btn-outline-success"><i class="bi bi-download"></i> Export</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr><th>Order #</th><th>Date</th><th>Prepared By</th><th>Priority</th><th class="text-end">Action</th></tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td><strong>{{ $item->id }}</strong></td>
                                <td>{{ $item->order_date }}</td>
                                <td>{{ $item->preparedBy->name ?? '—' }}</td>
                                <td>{{ ucfirst($item->priority_status) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('storage_supplies.store_ordering.edit', $item->id) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-pencil"></i> Edit Items
                                    </a>
                                    <button class="btn btn-sm btn-warning js-approve" data-id="{{ $item->id }}">Approve</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No pending orders.</td></tr>
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

<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="approveForm">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="approveOrderId">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" id="approveUsername" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" id="approvePassword" class="form-control" required>
                    </div>
                    <div class="text-danger small" id="approveFormError"></div>
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
$(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    const modal = new bootstrap.Modal(document.getElementById('approveModal'));

    $('.js-approve').on('click', function () {
        $('#approveOrderId').val($(this).data('id'));
        $('#approveForm')[0].reset();
        $('#approveFormError').text('');
        modal.show();
    });

    $('#approveForm').on('submit', function (e) {
        e.preventDefault();
        $.post(`/storage-supplies/store-ordering/${$('#approveOrderId').val()}/approve`, {
            username: $('#approveUsername').val(),
            password: $('#approvePassword').val(),
        })
            .done(() => location.reload())
            .fail(xhr => $('#approveFormError').text(xhr.responseJSON?.message || 'Approval failed.'));
    });
});
</script>
@endsection
