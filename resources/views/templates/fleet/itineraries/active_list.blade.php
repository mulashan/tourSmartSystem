@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Active Trips</h2></div>
<table class="table table-hover" data-datatable data-export-name="active-trips">
    <thead><tr><th>Trip No.</th><th>Vehicle</th><th>Driver</th><th>Status</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($itineraries as $i)
            <tr data-id="{{ $i->id }}">
                <td>{{ $i->trip_number }}</td>
                <td>{{ $i->vehicle->registration_no ?? '—' }}</td>
                <td>{{ $i->driver->Employee_Name ?? '—' }}</td>
                <td><span class="badge bg-info text-dark">{{ ucwords(str_replace('_', ' ', $i->status)) }}</span></td>
                <td class="text-end">
                    @if($i->status === 'assigned')<button class="btn btn-sm btn-dark js-ready" data-id="{{ $i->id }}">Mark Ready</button>@endif
                    @if($i->status === 'ready')<button class="btn btn-sm btn-primary js-in-progress" data-id="{{ $i->id }}">Start Trip</button>@endif
                    @if($i->status === 'in_progress' && ! $i->gatePass)
                        <button class="btn btn-sm btn-success js-generate-gp" data-id="{{ $i->id }}">Generate Gate Pass</button>
                        <button class="btn btn-sm btn-outline-dark js-add-leg" data-id="{{ $i->id }}">Add Leg</button>
                    @endif
                    @if($i->status === 'completed')<button class="btn btn-sm btn-success js-close" data-id="{{ $i->id }}">Close Trip</button>@endif
                    @if(in_array($i->status, ['assigned', 'ready', 'in_progress']))<button class="btn btn-sm btn-outline-warning js-reassign" data-id="{{ $i->id }}">Reassign</button>@endif
                    @if(in_array($i->status, ['assigned', 'ready']))<button class="btn btn-sm btn-outline-danger js-cancel" data-id="{{ $i->id }}">Cancel</button>@endif
                    <a href="{{ route('fleet.itineraries.preview', $i->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted">No active trips.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="modal fade" id="reassignModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form id="reassignForm">
        <div class="modal-header"><h5 class="modal-title">Reassign Vehicle & Driver</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" id="reassignId">
            <div class="mb-3"><label class="form-label">Vehicle *</label><select id="reassignVehicle" class="form-select" required><option value="">Select...</option>@foreach($vehicles as $v)<option value="{{ $v->id }}">{{ $v->registration_no }} ({{ ucfirst($v->status) }})</option>@endforeach</select></div>
            <div class="mb-3"><label class="form-label">Driver *</label><select id="reassignDriver" class="form-select" required><option value="">Select...</option>@foreach($drivers as $d)<option value="{{ $d->Employee_ID }}">{{ $d->Employee_Name }}</option>@endforeach</select></div>
            <div class="text-danger small" id="reassignFormError"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Reassign</button></div>
    </form>
</div></div></div>

<div class="modal fade" id="addLegModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form id="addLegForm">
        <div class="modal-header"><h5 class="modal-title">Add Leg</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" id="addLegItineraryId">
            <div class="mb-3">
                <label class="form-label">Start *</label>
                <select id="legStart" class="form-select" required>
                    <option value="">Select...</option>
                    @foreach($destinations as $d)<option value="{{ $d->name }}">{{ $d->name }}</option>@endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Destination *</label>
                <select id="legDest" class="form-select" required>
                    <option value="">Select...</option>
                    @foreach($destinations as $d)<option value="{{ $d->name }}">{{ $d->name }}</option>@endforeach
                </select>
            </div>
            <div class="mb-3"><label class="form-label">Date</label><input type="date" id="legDate" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Notes</label><input type="text" id="legNotes" class="form-control"></div>
            <div class="text-danger small" id="addLegFormError"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add Leg</button></div>
    </form>
</div></div></div>

<div class="modal fade" id="closeTripModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form id="closeTripForm">
        <div class="modal-header"><h5 class="modal-title">Close Trip</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" id="closeTripId">
            <div class="mb-3"><label class="form-label">Return Odometer Reading *</label><input type="number" id="closeOdometer" class="form-control" min="0" required></div>
            <div class="text-danger small" id="closeTripFormError"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Close Trip</button></div>
    </form>
</div></div></div>

<div class="modal fade" id="generateGpModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form id="generateGpForm">
        <div class="modal-header"><h5 class="modal-title">Generate Gate Pass</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" id="gpItineraryId">
            <div class="alert alert-info small">Generating this Gate Pass will mark the trip as <strong>Completed</strong>.</div>
            <div class="mb-3"><label class="form-label">Expected Return</label><input type="datetime-local" id="gpExpectedReturn" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Fuel Level</label><input type="text" id="gpFuelLevel" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Passengers/Tourists</label><input type="text" id="gpPassengers" class="form-control"></div>
            <div class="text-danger small" id="generateGpFormError"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Generate & Complete Trip</button></div>
    </form>
</div></div></div>
@endsection

@section('scripts')
<script>
(function whenJQueryReady(fn) { if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); } })(function () {
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        function post(url, data) { return $.post(url, data || {}).done(() => location.reload()).fail(xhr => Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || 'Something went wrong.' })); }

        $('.js-ready').on('click', function () { post(`/fleet/itineraries/${$(this).data('id')}/ready`); });
        $('.js-in-progress').on('click', function () { post(`/fleet/itineraries/${$(this).data('id')}/in-progress`); });

        const reassignModal = new bootstrap.Modal(document.getElementById('reassignModal'));
        const addLegModal = new bootstrap.Modal(document.getElementById('addLegModal'));
        const closeTripModal = new bootstrap.Modal(document.getElementById('closeTripModal'));
        const generateGpModal = new bootstrap.Modal(document.getElementById('generateGpModal'));

        $('.js-reassign').on('click', function () { $('#reassignId').val($(this).data('id')); $('#reassignForm')[0].reset(); $('#reassignFormError').text(''); reassignModal.show(); });
        $('#reassignForm').on('submit', function (e) {
            e.preventDefault();
            $.post(`/fleet/itineraries/${$('#reassignId').val()}/reassign`, { vehicle_id: $('#reassignVehicle').val(), driver_employee_id: $('#reassignDriver').val() })
                .done(() => Swal.fire({ icon: 'success', title: 'Reassigned', timer: 1200, showConfirmButton: false }).then(() => location.reload()))
                .fail(xhr => $('#reassignFormError').text(xhr.responseJSON?.message || 'Something went wrong.'));
        });

        $('.js-add-leg').on('click', function () { $('#addLegItineraryId').val($(this).data('id')); $('#addLegForm')[0].reset(); $('#addLegFormError').text(''); addLegModal.show(); });
        $('#addLegForm').on('submit', function (e) {
            e.preventDefault();
            $.post(`/fleet/itineraries/${$('#addLegItineraryId').val()}/legs`, { start_point: $('#legStart').val(), destination: $('#legDest').val(), leg_date: $('#legDate').val(), notes: $('#legNotes').val() })
                .done(() => Swal.fire({ icon: 'success', title: 'Leg added — remember to assign fuel for it', timer: 2000, showConfirmButton: false }).then(() => location.reload()))
                .fail(xhr => $('#addLegFormError').text(xhr.responseJSON?.message || 'Something went wrong.'));
        });

        $('.js-close').on('click', function () { $('#closeTripId').val($(this).data('id')); $('#closeTripForm')[0].reset(); $('#closeTripFormError').text(''); closeTripModal.show(); });
        $('#closeTripForm').on('submit', function (e) {
            e.preventDefault();
            $.post(`/fleet/itineraries/${$('#closeTripId').val()}/close`, { return_odometer: $('#closeOdometer').val() })
                .done(() => Swal.fire({ icon: 'success', title: 'Trip closed', timer: 1200, showConfirmButton: false }).then(() => location.reload()))
                .fail(xhr => $('#closeTripFormError').text(xhr.responseJSON?.message || 'Something went wrong.'));
        });

        $('.js-generate-gp').on('click', function () { $('#gpItineraryId').val($(this).data('id')); $('#generateGpForm')[0].reset(); $('#generateGpFormError').text(''); generateGpModal.show(); });
        $('#generateGpForm').on('submit', function (e) {
            e.preventDefault();
            $.post(`/fleet/gate-pass/${$('#gpItineraryId').val()}/generate`, { expected_return: $('#gpExpectedReturn').val(), fuel_level: $('#gpFuelLevel').val(), passengers: $('#gpPassengers').val() })
                .done(response => { window.location.href = `/fleet/gate-pass/${response.id}/preview`; })
                .fail(xhr => $('#generateGpFormError').text(xhr.responseJSON?.message || 'Something went wrong.'));
        });

        $('.js-cancel').on('click', function () {
            const id = $(this).data('id');
            Swal.fire({ title: 'Cancel this trip?', input: 'text', inputLabel: 'Reason', showCancelButton: true, confirmButtonText: 'Cancel Trip', inputValidator: v => !v ? 'Reason required' : undefined }).then(result => {
                if (result.isConfirmed) post(`/fleet/itineraries/${id}/cancel`, { reason: result.value });
            });
        });
    });
});
</script>
@endsection