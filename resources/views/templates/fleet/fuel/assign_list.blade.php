@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Assign Itinerary Fuel</h2></div>

<table class="table table-hover" data-datatable data-export-name="fuel-assign-queue">
    <thead><tr><th>Trip No.</th><th>Vehicle</th><th>Type</th><th>Leg</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($queue as $q)
            <tr>
                <td>{{ $q['itinerary']->trip_number }}</td>
                <td>{{ $q['itinerary']->vehicle->registration_no ?? '—' }}</td>
                <td>{{ $q['type'] === 'main' ? 'Main Trip' : 'Leg' }}</td>
                <td>{{ $q['leg'] ? "Leg {$q['leg']->leg_number}: {$q['leg']->start_point} → {$q['leg']->destination}" : '—' }}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-info text-white js-assign" data-itinerary-id="{{ $q['itinerary']->id }}" data-leg-id="{{ $q['leg']->id ?? '' }}">Assign Fuel</button>
                </td>
            </tr>
        @empty
        @endforelse
    </tbody>
</table>

<div class="modal fade" id="assignFuelModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form id="assignFuelForm">
        <div class="modal-header"><h5 class="modal-title">Assign Fuel</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" id="fuelItineraryId"><input type="hidden" id="fuelLegId">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Fuel Source *</label><select id="fuelSource" class="form-select" required><option value="">Select...</option>@foreach($fuelSources as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Fuel Type *</label><select id="fuelType" class="form-select" required><option value="Petrol">Petrol</option><option value="Diesel">Diesel</option></select></div>
                <div class="col-md-4"><label class="form-label">Quantity *</label><input type="number" step="0.01" id="fuelQty" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label">Unit Price *</label><input type="number" step="0.01" id="fuelPrice" class="form-control" required></div>
                <div class="col-md-4" id="fuelOdometerWrap"><label class="form-label">Odometer</label><input type="number" id="fuelOdometer" class="form-control"></div>
                <div class="col-12"><label class="form-label">Remarks</label><input type="text" id="fuelRemarks" class="form-control"></div>
            </div>
            <div class="text-danger small mt-2" id="assignFuelFormError"></div>
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
        const modal = new bootstrap.Modal(document.getElementById('assignFuelModal'));

        $('.js-assign').on('click', function () {
            $('#fuelItineraryId').val($(this).data('itinerary-id'));
            $('#fuelLegId').val($(this).data('leg-id'));
            $('#assignFuelForm')[0].reset();
            $('#assignFuelFormError').text('');

            const isLeg = !! $(this).data('leg-id');
            $('#fuelOdometerWrap').toggleClass('d-none', isLeg);
            if (isLeg) $('#fuelOdometer').val('');

            modal.show();
        });

        $('#assignFuelForm').on('submit', function (e) {
            e.preventDefault();
            $.post(`/fleet/fuel/assign/${$('#fuelItineraryId').val()}`, {
                leg_id: $('#fuelLegId').val() || null, fuel_source_id: $('#fuelSource').val(), fuel_type: $('#fuelType').val(),
                quantity_assigned: $('#fuelQty').val(), unit_price: $('#fuelPrice').val(), odometer_reading: $('#fuelOdometer').val(), remarks: $('#fuelRemarks').val(),
            })
                .done(() => Swal.fire({ icon: 'success', title: 'Fuel assigned', timer: 1200, showConfirmButton: false }).then(() => location.reload()))
                .fail(xhr => $('#assignFuelFormError').text(xhr.responseJSON?.message || 'Something went wrong.'));
        });
    });
});
</script>
@endsection