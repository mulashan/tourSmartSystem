@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Accidents & Road Fines</h2><button class="btn btn-info text-white" id="js-add-incident">Add Record</button></div>

<table class="table table-hover" data-datatable data-export-name="incidents">
    <thead><tr><th>Type</th><th>Vehicle</th><th>Driver</th><th>Date</th><th>Est. Cost</th><th>Actual Cost</th><th>Status</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($incidents as $i)
            <tr>
                <td>{{ $i->type === 'accident' ? 'Accident' : 'Road Fine' }}</td>
                <td>{{ $i->vehicle->registration_no ?? '—' }}</td>
                <td>{{ $i->driver->Employee_Name ?? '—' }}</td>
                <td>{{ $i->incident_date }}</td>
                <td>{{ $i->estimated_cost ? number_format($i->estimated_cost, 2) : '—' }}</td>
                <td>{{ $i->actual_cost ? number_format($i->actual_cost, 2) : '—' }}</td>
                <td><span class="badge bg-{{ $i->status === 'open' ? 'warning text-dark' : 'success' }}">{{ ucfirst($i->status) }}</span></td>
                <td class="text-end"><a href="{{ route('fleet.incidents.show', $i->id) }}" class="btn btn-sm btn-outline-primary">View</a></td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted">No incidents recorded.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="modal fade" id="addIncidentModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <form id="addIncidentForm" enctype="multipart/form-data">
        <div class="modal-header"><h5 class="modal-title">Add Accident / Road Fine</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Type *</label><select id="incType" class="form-select" required><option value="accident">Accident</option><option value="road_fine">Road Fine</option></select></div>
                <div class="col-md-4"><label class="form-label">Vehicle *</label><select id="incVehicle" class="form-select" required><option value="">Select...</option>@foreach($vehicles as $v)<option value="{{ $v->id }}">{{ $v->registration_no }}</option>@endforeach</select></div>
                <div class="col-md-4"><label class="form-label">Date *</label><input type="date" id="incDate" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Location</label><input type="text" id="incLocation" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Police Report No.</label><input type="text" id="incPolice" class="form-control"></div>
                <div class="col-12"><label class="form-label">Description</label><textarea id="incDescription" class="form-control" rows="2"></textarea></div>
                <div class="col-md-6"><label class="form-label">Injuries</label><textarea id="incInjuries" class="form-control" rows="2"></textarea></div>
                <div class="col-md-6"><label class="form-label">Damages</label><textarea id="incDamages" class="form-control" rows="2"></textarea></div>
                <div class="col-md-4"><label class="form-label">Covered By</label><select id="incCoveredBy" class="form-select"><option value="">—</option><option value="company">Company</option><option value="insurance">Insurance</option><option value="driver">Driver</option></select></div>
                <div class="col-md-4"><label class="form-label">Estimated Cost</label><input type="number" step="0.01" id="incEstCost" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">Actual Cost</label><input type="number" step="0.01" id="incActualCost" class="form-control"></div>
                <div class="col-12"><label class="form-label">Photos</label><input type="file" id="incPhotos" class="form-control" multiple accept="image/*"></div>
            </div>
            <div class="text-danger small mt-2" id="addIncidentFormError"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>
@endsection

@section('scripts')
<script>
(function whenJQueryReady(fn) { if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); } })(function () {
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
        const modal = new bootstrap.Modal(document.getElementById('addIncidentModal'));

        $('#js-add-incident').on('click', () => { $('#addIncidentForm')[0].reset(); $('#addIncidentFormError').text(''); modal.show(); });

        $('#addIncidentForm').on('submit', function (e) {
            e.preventDefault();
            const formData = new FormData();
            formData.append('type', $('#incType').val());
            formData.append('vehicle_id', $('#incVehicle').val());
            formData.append('incident_date', $('#incDate').val());
            formData.append('location', $('#incLocation').val());
            formData.append('police_report', $('#incPolice').val());
            formData.append('description', $('#incDescription').val());
            formData.append('injuries', $('#incInjuries').val());
            formData.append('damages', $('#incDamages').val());
            formData.append('covered_by', $('#incCoveredBy').val());
            formData.append('estimated_cost', $('#incEstCost').val());
            formData.append('actual_cost', $('#incActualCost').val());
            Array.from($('#incPhotos')[0].files).forEach(f => formData.append('photos[]', f));

            $.ajax({ url: '{{ route("fleet.incidents.store") }}', method: 'POST', data: formData, processData: false, contentType: false })
                .done(() => Swal.fire({ icon: 'success', title: 'Saved', timer: 1200, showConfirmButton: false }).then(() => location.reload()))
                .fail(xhr => {
                    const errors = xhr.responseJSON?.errors;
                    $('#addIncidentFormError').text(errors ? Object.values(errors)[0][0] : (xhr.responseJSON?.message || 'Something went wrong.'));
                });
        });
    });
});
</script>
@endsection