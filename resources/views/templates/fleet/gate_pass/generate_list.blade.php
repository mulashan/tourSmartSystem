@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Generate Gate Pass</h2></div>

<table class="table table-hover" data-datatable data-export-name="gate-pass-eligible">
    <thead><tr><th>Trip No.</th><th>Vehicle</th><th>Driver</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($eligible as $i)
            <tr>
                <td>{{ $i->trip_number }}</td><td>{{ $i->vehicle->registration_no ?? '—' }}</td><td>{{ $i->driver->Employee_Name ?? '—' }}</td>
                <td class="text-end"><button class="btn btn-sm btn-info text-white js-generate" data-id="{{ $i->id }}">Generate Gate Pass</button></td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted">No trips currently eligible (fuel must be issued first).</td></tr>
        @endforelse
    </tbody>
</table>

<div class="modal fade" id="generateModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form id="generateForm">
        <div class="modal-header"><h5 class="modal-title">Generate Gate Pass</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" id="generateItineraryId">
            <div class="alert alert-info small">Generating this Gate Pass will mark the trip as <strong>Completed</strong>.</div>
            <div class="mb-3"><label class="form-label">Expected Return</label><input type="datetime-local" id="expectedReturn" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Fuel Level</label><input type="text" id="fuelLevel" class="form-control" placeholder="e.g. Full, 3/4"></div>
            <div class="mb-3"><label class="form-label">Passengers/Tourists</label><input type="text" id="passengers" class="form-control"></div>
            <div class="text-danger small" id="generateFormError"></div>
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
        const modal = new bootstrap.Modal(document.getElementById('generateModal'));

        $('.js-generate').on('click', function () { $('#generateItineraryId').val($(this).data('id')); $('#generateForm')[0].reset(); $('#generateFormError').text(''); modal.show(); });

        $('#generateForm').on('submit', function (e) {
            e.preventDefault();
            $.post(`/fleet/gate-pass/${$('#generateItineraryId').val()}/generate`, {
                expected_return: $('#expectedReturn').val(), fuel_level: $('#fuelLevel').val(), passengers: $('#passengers').val(),
            })
                .done(response => { window.location.href = `/fleet/gate-pass/${response.id}/preview`; })
                .fail(xhr => $('#generateFormError').text(xhr.responseJSON?.message || 'Something went wrong.'));
        });
    });
});
</script>
@endsection