@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="settings-panel-head">
    <h2>{{ $incident->type === 'accident' ? 'Accident' : 'Road Fine' }} — {{ $incident->vehicle->registration_no ?? '—' }}</h2>
    @if($incident->status === 'open')<button class="btn btn-success" id="js-close-incident">Close Record</button>@endif
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3"><strong>Driver:</strong><br>{{ $incident->driver->Employee_Name ?? '—' }}</div>
    <div class="col-md-3"><strong>Date:</strong><br>{{ $incident->incident_date }}</div>
    <div class="col-md-3"><strong>Location:</strong><br>{{ $incident->location ?? '—' }}</div>
    <div class="col-md-3"><strong>Status:</strong><br>{{ ucfirst($incident->status) }}</div>
    <div class="col-md-3"><strong>Police Report:</strong><br>{{ $incident->police_report ?? '—' }}</div>
    <div class="col-md-3"><strong>Covered By:</strong><br>{{ $incident->covered_by ? ucfirst($incident->covered_by) : '—' }}</div>
    <div class="col-md-3"><strong>Estimated Cost:</strong><br>{{ $incident->estimated_cost ? number_format($incident->estimated_cost, 2) : '—' }}</div>
    <div class="col-md-3"><strong>Actual Cost:</strong><br>{{ $incident->actual_cost ? number_format($incident->actual_cost, 2) : '—' }}</div>
    <div class="col-12"><strong>Description:</strong><br>{{ $incident->description ?? '—' }}</div>
    <div class="col-md-6"><strong>Injuries:</strong><br>{{ $incident->injuries ?? '—' }}</div>
    <div class="col-md-6"><strong>Damages:</strong><br>{{ $incident->damages ?? '—' }}</div>
</div>

@if($incident->photos->isNotEmpty())
    <div class="settings-panel-head"><h4>Photos</h4></div>
    <div class="d-flex gap-2 flex-wrap">
        @foreach($incident->photos as $p)
            <a href="{{ asset('storage/' . $p->path) }}" target="_blank"><img src="{{ asset('storage/' . $p->path) }}" style="height:120px;border-radius:6px;"></a>
        @endforeach
    </div>
@endif
@endsection

@section('scripts')
<script>
(function whenJQueryReady(fn) { if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); } })(function () {
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
        $('#js-close-incident').on('click', function () {
            Swal.fire({ title: 'Close this record?', input: 'number', inputLabel: 'Actual Cost (optional)', showCancelButton: true, confirmButtonText: 'Close' }).then(result => {
                if (! result.isConfirmed) return;
                $.post('{{ route("fleet.incidents.close", $incident->id) }}', { actual_cost: result.value })
                    .done(() => Swal.fire({ icon: 'success', title: 'Closed', timer: 1000, showConfirmButton: false }).then(() => location.reload()))
                    .fail(xhr => Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message }));
            });
        });
    });
});
</script>
@endsection