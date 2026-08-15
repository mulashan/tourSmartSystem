@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="settings-panel-head">
    <h2>Maintenance Order</h2>
    <button type="button" class="btn btn-info text-white" id="js-add-order"><i class="bi bi-plus-lg"></i> New Maintenance Order</button>
</div>

<div class="table-responsive">
    <table class="table table-hover" data-datatable data-export-name="maintenance-orders">
        <thead>
            <tr><th>Vehicle</th><th>Driver</th><th>Problem</th><th>Workshop</th><th>Status</th><th>Created By</th><th class="text-end">Action</th></tr>
        </thead>
        <tbody>
            @forelse($orders as $o)
                <tr>
                    <td>{{ $o->vehicle->registration_no ?? '—' }}</td>
                    <td>{{ $o->driver->Employee_Name ?? '—' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($o->problem, 60) }}</td>
                    <td>{{ $o->workshop->Subdepartment_Name ?? '—' }}</td>
                    <td><span class="badge bg-{{ $o->status === 'open' ? 'warning text-dark' : ($o->status === 'completed' ? 'success' : 'secondary') }}">{{ ucfirst($o->status) }}</span></td>
                    <td>{{ $o->createdBy->name ?? '—' }}</td>
                    <td class="text-end">
                        @if($o->status === 'open')
                            <button type="button" class="btn btn-sm btn-success js-complete-order" data-id="{{ $o->id }}">Complete</button>
                            <button type="button" class="btn btn-sm btn-outline-danger js-cancel-order" data-id="{{ $o->id }}">Cancel</button>
                        @endif
                    </td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>

<div class="modal fade" id="addOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addOrderForm">
                <div class="modal-header"><h5 class="modal-title">New Maintenance Order</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Vehicle *</label>
                        <select id="orderVehicle" class="form-select" required>
                            <option value="">Select...</option>
                            @foreach($vehicles as $v)<option value="{{ $v->id }}">{{ $v->registration_no }} — {{ $v->make }} {{ $v->model }}</option>@endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Driver</label>
                        <input type="text" id="orderDriverDisplay" class="form-control" disabled placeholder="Defaults to assigned driver">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Problem *</label>
                        <textarea id="orderProblem" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Workshop *</label>
                        <select id="orderWorkshop" class="form-select" required>
                            <option value="">Select...</option>
                            @foreach($workshops as $w)<option value="{{ $w->Subdepartment_ID }}">{{ $w->Subdepartment_Name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="text-danger small" id="addOrderFormError"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    window.vehiclesData = @json($vehicles->map(fn ($v) => ['id' => $v->id, 'driver' => $v->assignedDriver->Employee_Name ?? 'None assigned']));
</script>
<script>
(function whenJQueryReady(fn) {
    if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); }
})(function () {
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
        const addModal = new bootstrap.Modal(document.getElementById('addOrderModal'));

        $('#js-add-order').on('click', () => { $('#addOrderForm')[0].reset(); $('#orderDriverDisplay').val(''); $('#addOrderFormError').text(''); addModal.show(); });

        $('#orderVehicle').on('change', function () {
            const v = window.vehiclesData.find(x => x.id == $(this).val());
            $('#orderDriverDisplay').val(v ? v.driver : '');
        });

        $('#addOrderForm').on('submit', function (e) {
            e.preventDefault();
            $.post('{{ route("fleet.maintenance.store") }}', {
                vehicle_id: $('#orderVehicle').val(), problem: $('#orderProblem').val(), workshop_subdepartment_id: $('#orderWorkshop').val(),
            })
                .done(() => Swal.fire({ icon: 'success', title: 'Saved', timer: 1200, showConfirmButton: false }).then(() => location.reload()))
                .fail(xhr => $('#addOrderFormError').text(xhr.responseJSON?.message || 'Something went wrong.'));
        });

        $('.js-complete-order').on('click', function () {
            const id = $(this).data('id');
            Swal.fire({ icon: 'question', title: 'Complete this order?', input: 'text', inputLabel: 'Notes (optional)', showCancelButton: true, confirmButtonText: 'Complete' }).then(result => {
                if (! result.isConfirmed) return;
                $.post(`/fleet/maintenance/${id}/complete`, { completion_notes: result.value })
                    .done(() => Swal.fire({ icon: 'success', title: 'Completed', timer: 1000, showConfirmButton: false }).then(() => location.reload()))
                    .fail(xhr => Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message }));
            });
        });

        $('.js-cancel-order').on('click', function () {
            const id = $(this).data('id');
            Swal.fire({ icon: 'warning', title: 'Cancel this order?', input: 'text', inputLabel: 'Reason', showCancelButton: true, confirmButtonText: 'Cancel Order', inputValidator: v => !v ? 'Reason required' : undefined }).then(result => {
                if (! result.isConfirmed) return;
                $.post(`/fleet/maintenance/${id}/cancel`, { completion_notes: result.value })
                    .done(() => Swal.fire({ icon: 'success', title: 'Cancelled', timer: 1000, showConfirmButton: false }).then(() => location.reload()))
                    .fail(xhr => Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message }));
            });
        });
    });
});
</script>
@endsection