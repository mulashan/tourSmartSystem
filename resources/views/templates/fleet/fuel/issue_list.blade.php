@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Issue Itinerary Fuel</h2></div>

<table class="table table-hover" data-datatable data-export-name="fuel-issue-queue">
    <thead><tr><th>Trip No.</th><th>Vehicle</th><th>Leg</th><th>Fuel Source</th><th>Assigned Qty</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($records as $r)
            <tr>
                <td>{{ $r->itinerary->trip_number ?? '—' }}</td>
                <td>{{ $r->vehicle->registration_no ?? '—' }}</td>
                <td>{{ $r->leg ? "Leg {$r->leg->leg_number}" : 'Main Trip' }}</td>
                <td>{{ $r->fuelSource->name ?? '—' }}</td>
                <td>{{ $r->quantity_assigned }}</td>
                <td class="text-end"><button class="btn btn-sm btn-success js-issue" data-id="{{ $r->id }}" data-qty="{{ $r->quantity_assigned }}">Issue</button></td>
            </tr>
        @empty
        @endforelse
    </tbody>
</table>
@endsection

@section('scripts')
<script>
(function whenJQueryReady(fn) { if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); } })(function () {
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
        $('.js-issue').on('click', function () {
            const id = $(this).data('id'), defaultQty = $(this).data('qty');
            Swal.fire({ title: 'Confirm Fuel Issued', input: 'number', inputValue: defaultQty, inputAttributes: { min: 0, step: 0.01 }, showCancelButton: true, confirmButtonText: 'Issue' }).then(result => {
                if (! result.isConfirmed) return;
                $.post(`/fleet/fuel/issue/${id}`, { issued_quantity: result.value })
                    .done(() => Swal.fire({ icon: 'success', title: 'Issued', timer: 1000, showConfirmButton: false }).then(() => location.reload()))
                    .fail(xhr => Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message }));
            });
        });
    });
});
</script>
@endsection