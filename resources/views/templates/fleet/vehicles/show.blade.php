@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="settings-panel-head">
    <h2>{{ $vehicle->vehicle_code }} — {{ $vehicle->registration_no }}</h2>
    <a href="{{ route('fleet.vehicles.edit', $vehicle->id) }}" class="btn btn-outline-secondary">Edit Vehicle</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3"><strong>Make/Model:</strong><br>{{ trim("{$vehicle->make} {$vehicle->model}") ?: '—' }}</div>
    <div class="col-md-2"><strong>Year:</strong><br>{{ $vehicle->year ?? '—' }}</div>
    <div class="col-md-2"><strong>Status:</strong><br>{{ ucwords(str_replace('_', ' ', $vehicle->status)) }}</div>
    <div class="col-md-2"><strong>Odometer:</strong><br>{{ number_format($vehicle->current_odometer) }} km</div>
    <div class="col-md-3"><strong>Location:</strong><br>{{ $vehicle->currentLocation->name ?? '—' }}</div>

    <div class="col-md-3"><strong>Ownership:</strong><br>{{ $vehicle->ownershipType->name ?? '—' }}</div>
    <div class="col-md-3"><strong>Owner:</strong><br>{{ $vehicle->owner ?? '—' }}</div>
    <div class="col-md-3"><strong>Chassis No.:</strong><br>{{ $vehicle->chassis_no ?? '—' }}</div>
    <div class="col-md-3"><strong>Engine No.:</strong><br>{{ $vehicle->engine_no ?? '—' }}</div>
</div>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Assigned Driver: <strong>{{ $vehicle->assignedDriver->Employee_Name ?? ($vehicle->assignedDriver ? '' : 'None') }}</strong></span>
        <button type="button" class="btn btn-sm btn-info text-white" id="js-open-assign-driver">Change Driver</button>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">Driver Assignment History</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0" data-datatable data-export-name="driver-history">
            <thead><tr><th>Driver</th><th>Assigned At</th><th>Unassigned At</th><th>Assigned By</th></tr></thead>
            <tbody>
                @forelse($vehicle->driverHistory as $h)
                    <tr>
                        <td>{{ $h->employee->Employee_Name ?? '—' }}</td>
                        <td>{{ $h->assigned_at }}</td>
                        <td>{{ $h->unassigned_at ?? 'Current' }}</td>
                        <td>{{ $h->assignedBy->name ?? '—' }}</td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Rental Agreements</span>
        <button type="button" class="btn btn-sm btn-info text-white" id="js-open-rental">Add Rental Agreement</button>
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0" data-datatable data-export-name="rental-agreements">
            <thead><tr><th>Owner</th><th>Start Date</th><th>End Date</th><th>Contact</th><th>Document</th></tr></thead>
            <tbody>
                @forelse($vehicle->rentalAgreements as $a)
                    <tr>
                        <td>{{ $a->owner }}</td>
                        <td>{{ $a->start_date }}</td>
                        <td>{{ $a->end_date ?? '—' }}</td>
                        <td>{{ $a->contact_info }}</td>
                        <td>@if($a->agreement_document)<a href="{{ asset('storage/' . $a->agreement_document) }}" target="_blank">View</a>@else — @endif</td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="assignDriverModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="assignDriverForm">
                <div class="modal-header"><h5 class="modal-title">Change Assigned Driver</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <select id="driverSelect" class="form-select">
                        <option value="">None (unassign)</option>
                        @foreach($drivers as $d)
                            <option value="{{ $d->Employee_ID }}" {{ $vehicle->assigned_driver_employee_id == $d->Employee_ID ? 'selected' : '' }}>{{ $d->Employee_Name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="rentalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rentalForm" enctype="multipart/form-data">
                <div class="modal-header"><h5 class="modal-title">Add Rental Agreement</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Owner *</label><input type="text" id="rentalOwner" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Start Date *</label><input type="date" id="rentalStartDate" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">End Date</label><input type="date" id="rentalEndDate" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Contact Information</label><input type="text" id="rentalContact" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Agreement Document</label><input type="file" id="rentalDocument" class="form-control"></div>
                    <div class="text-danger small" id="rentalFormError"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
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

        const assignModal = new bootstrap.Modal(document.getElementById('assignDriverModal'));
        const rentalModal = new bootstrap.Modal(document.getElementById('rentalModal'));

        $('#js-open-assign-driver').on('click', () => assignModal.show());
        $('#js-open-rental').on('click', () => rentalModal.show());

        $('#assignDriverForm').on('submit', function (e) {
            e.preventDefault();
            $.post('{{ route("fleet.vehicles.assign_driver", $vehicle->id) }}', { employee_id: $('#driverSelect').val() })
                .done(() => Swal.fire({ icon: 'success', title: 'Driver updated', timer: 1200, showConfirmButton: false }).then(() => location.reload()))
                .fail(xhr => Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || 'Something went wrong.' }));
        });

        $('#rentalForm').on('submit', function (e) {
            e.preventDefault();

            const formData = new FormData();
            formData.append('owner', $('#rentalOwner').val());
            formData.append('start_date', $('#rentalStartDate').val());
            formData.append('end_date', $('#rentalEndDate').val());
            formData.append('contact_info', $('#rentalContact').val());
            if ($('#rentalDocument')[0].files[0]) formData.append('agreement_document', $('#rentalDocument')[0].files[0]);

            $.ajax({
                url: '{{ route("fleet.vehicles.rental_agreement", $vehicle->id) }}',
                method: 'POST', data: formData, processData: false, contentType: false,
            })
                .done(() => Swal.fire({ icon: 'success', title: 'Saved', timer: 1200, showConfirmButton: false }).then(() => location.reload()))
                .fail(xhr => {
                    const errors = xhr.responseJSON?.errors;
                    $('#rentalFormError').text(errors ? Object.values(errors)[0][0] : (xhr.responseJSON?.message || 'Something went wrong.'));
                });
        });
    });
});
</script>
@endsection