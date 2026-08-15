@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Itinerary Fuel</h2></div>

<table class="table table-hover mb-4" data-datatable data-export-name="trip-fuel">
    <thead><tr><th>Trip</th><th>Vehicle</th><th>Fuel Source</th><th>Qty Assigned</th><th>Qty Issued</th><th>Total</th><th>Status</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($fuelRecords as $f)
            <tr>
                <td>{{ $f->itinerary->trip_number ?? '—' }}</td>
                <td>{{ $f->vehicle->registration_no ?? '—' }}</td>
                <td>{{ $f->fuelSource->name ?? '—' }}</td>
                <td>{{ $f->quantity_assigned }}</td>
                <td>{{ $f->issued_quantity ?? '—' }}</td>
                <td>{{ number_format($f->total_amount, 2) }}</td>
                <td><span class="badge bg-{{ $f->status === 'issued' ? 'success' : 'warning text-dark' }}">{{ ucfirst($f->status) }}</span></td>
                <td class="text-end">
                    @if($f->status === 'assigned')<button class="btn btn-sm btn-success js-issue-fuel" data-id="{{ $f->id }}" data-qty="{{ $f->quantity_assigned }}">Issue</button>@endif
                </td>
            </tr>
        @empty
        @endforelse
    </tbody>
</table>

<div class="settings-panel-head"><h2>Assign New Trip Fuel</h2></div>
<table class="table table-hover" data-datatable data-export-name="assignable-trips">
    <thead><tr><th>Trip No.</th><th>Vehicle</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($itineraries as $i)
            <tr>
                <td>{{ $i->trip_number }}</td><td>{{ $i->vehicle->registration_no ?? 'Not assigned' }}</td>
                <td class="text-end"><button class="btn btn-sm btn-info text-white js-assign-fuel" data-id="{{ $i->id }}">Assign Fuel</button></td>
            </tr>
        @empty
        @endforelse
    </tbody>
</table>

<div class="modal fade" id="assignFuelModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form id="assignFuelForm">
        <div class="modal-header"><h5 class="modal-title">Assign Fuel</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" id="fuelItineraryId">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Fuel Source *</label>
                    <select id="fuelSource" class="form-select" required><option value="">Select...</option>@foreach($fuelSources as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Fuel Type *</label>
                    <select id="fuelType" class="form-select" required><option value="Petrol">Petrol</option><option value="Diesel">Diesel</option></select>
                </div>
                <div class="col-md-4"><label class="form-label">Quantity *</label><input type="number" step="0.01" id="fuelQty" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label">Unit Price *</label><input type="number" step="0.01" id="fuelPrice" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label">Odometer Reading</label><input type="number" id="fuelOdometer" class="form-control"></div>
                <div class="col-12"><label class="form-label">Remarks</label><input type="text" id="fuelRemarks" class="form-control" placeholder="e.g. reroute top-up"></div>
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
        const assignModal = new bootstrap.Modal(document.getElementById('assignFuelModal'));

        $('.js-assign-fuel').on('click', function () { $('#fuelItineraryId').val($(this).data('id')); $('#assignFuelForm')[0].reset(); $('#assignFuelFormError').text(''); assignModal.show(); });

        $('#assignFuelForm').on('submit', function (e) {
            e.preventDefault();
            $.post(`/fleet/fuel/trip/${$('#fuelItineraryId').val()}/assign`, {
                fuel_source_id: $('#fuelSource').val(), fuel_type: $('#fuelType').val(), quantity_assigned: $('#fuelQty').val(),
                unit_price: $('#fuelPrice').val(), odometer_reading: $('#fuelOdometer').val(), remarks: $('#fuelRemarks').val(),
            })
                .done(() => Swal.fire({ icon: 'success', title: 'Fuel assigned', timer: 1200, showConfirmButton: false }).then(() => location.reload()))
                .fail(xhr => $('#assignFuelFormError').text(xhr.responseJSON?.message || 'Something went wrong.'));
        });

        $('.js-issue-fuel').on('click', function () {
            const id = $(this).data('id'), defaultQty = $(this).data('qty');
            Swal.fire({ title: 'Confirm Fuel Issued', input: 'number', inputValue: defaultQty, inputAttributes: { min: 0, step: 0.01 }, showCancelButton: true, confirmButtonText: 'Issue' }).then(result => {
                if (! result.isConfirmed) return;
                $.post(`/fleet/fuel/trip/${id}/issue`, { issued_quantity: result.value })
                    .done(() => Swal.fire({ icon: 'success', title: 'Issued', timer: 1000, showConfirmButton: false }).then(() => location.reload()))
                    .fail(xhr => Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message }));
            });
        });
    });
});
</script>
@endsection