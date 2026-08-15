@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Assign Vehicle & Driver</h2></div>
<table class="table table-hover" data-datatable data-export-name="assign-itineraries">
    <thead><tr><th>Trip No.</th><th>Client(s)</th><th>Destination</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($itineraries as $i)
            <tr>
                <td>{{ $i->trip_number }}</td><td>{{ $i->clients }}</td><td>{{ $i->destination }}</td>
                <td class="text-end"><button class="btn btn-sm btn-info text-white js-open-assign" data-id="{{ $i->id }}">Assign Vehicle & Driver</button></td>
            </tr>
        @empty
        @endforelse
    </tbody>
</table>

<div class="modal fade" id="assignModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form id="assignForm">
        <div class="modal-header"><h5 class="modal-title">Assign Vehicle & Driver</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" id="assignItineraryId">
            <div class="mb-3">
                <label class="form-label">Vehicle *</label>
                <select id="assignVehicle" class="form-select" required>
                    <option value="">Select...</option>
                    @foreach($vehicles as $v)<option value="{{ $v->id }}">{{ $v->registration_no }} — {{ $v->make }} {{ $v->model }}</option>@endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Driver *</label>
                <select id="assignDriver" class="form-select" required>
                    <option value="">Select...</option>
                    @foreach($drivers as $d)<option value="{{ $d->Employee_ID }}">{{ $d->Employee_Name }}</option>@endforeach
                </select>
            </div>
            <div class="text-danger small" id="assignFormError"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Assign</button></div>
    </form>
</div></div></div>
@endsection

@section('scripts')
<script>
(function whenJQueryReady(fn) { if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); } })(function () {
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
        const modal = new bootstrap.Modal(document.getElementById('assignModal'));
        $('.js-open-assign').on('click', function () { $('#assignItineraryId').val($(this).data('id')); $('#assignForm')[0].reset(); $('#assignFormError').text(''); modal.show(); });
        $('#assignForm').on('submit', function (e) {
            e.preventDefault();
            $.post(`/fleet/itineraries/${$('#assignItineraryId').val()}/assign`, { vehicle_id: $('#assignVehicle').val(), driver_employee_id: $('#assignDriver').val() })
                .done(() => Swal.fire({ icon: 'success', title: 'Assigned', timer: 1200, showConfirmButton: false }).then(() => location.reload()))
                .fail(xhr => $('#assignFormError').text(xhr.responseJSON?.message || 'Assignment failed.'));
        });
    });
});
</script>
@endsection