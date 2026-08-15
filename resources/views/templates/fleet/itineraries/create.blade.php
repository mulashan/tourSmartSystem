@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<form id="itineraryForm">
    <div class="card mb-3">
        <div class="card-header">New Itinerary</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Client(s) *</label><input type="text" id="clients" class="form-control" required></div>
                <div class="col-md-4">
                    <label class="form-label">Destination *</label>
                    <select id="destination" class="form-select" required>
                        <option value="">Select...</option>
                        @foreach($destinations as $d)<option value="{{ $d->name }}">{{ $d->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Start *</label>
                    <select id="startPoint" class="form-select" required>
                        <option value="">Select...</option>
                        @foreach($destinations as $d)<option value="{{ $d->name }}" {{ $d->name === 'Arusha Office' ? 'selected' : '' }}>{{ $d->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Return *</label>
                    <select id="returnPoint" class="form-select" required>
                        <option value="">Select...</option>
                        @foreach($destinations as $d)<option value="{{ $d->name }}" {{ $d->name === 'Arusha Office' ? 'selected' : '' }}>{{ $d->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label">Start Date *</label><input type="date" id="startDate" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label">End Date *</label><input type="date" id="endDate" class="form-control" required></div>
                <div class="col-12"><label class="form-label">Comments</label><textarea id="comments" class="form-control" rows="2"></textarea></div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Itinerary Legs (optional — used to allocate fuel per leg)</span>
            <button type="button" class="btn btn-sm btn-dark" id="js-add-leg">Add Leg</button>
        </div>
        <div class="card-body">
            <table class="table table-sm" id="legsTable">
                <thead><tr><th>Leg</th><th>Start</th><th>Destination</th><th>Date</th><th>Notes</th><th></th></tr></thead>
                <tbody><tr class="js-empty-row"><td colspan="6" class="text-center text-muted">No legs added</td></tr></tbody>
            </table>
        </div>
    </div>

    <div class="text-end">
        <a href="{{ route('fleet.itineraries.new') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-info text-white">Save Itinerary</button>
    </div>
</form>
@endsection

@section('scripts')
<script>
(function whenJQueryReady(fn) {
    if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); }
})(function () {
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
        let legs = [];

        function renderLegs() {
            const $body = $('#legsTable tbody').empty();
            if (! legs.length) { $body.append('<tr class="js-empty-row"><td colspan="6" class="text-center text-muted">No legs added</td></tr>'); return; }
            legs.forEach((l, i) => {
                $body.append(`<tr><td>${i + 1}</td><td>${l.start_point}</td><td>${l.destination}</td><td>${l.leg_date || '—'}</td><td>${l.notes || ''}</td><td><button type="button" class="btn btn-sm btn-outline-danger js-remove-leg" data-i="${i}">✕</button></td></tr>`);
            });
        }

        $('#js-add-leg').on('click', function () {
            Swal.fire({
                title: 'Add Leg', html:
                    '<input id="swalStart" class="swal2-input" placeholder="Start">' +
                    '<input id="swalDest" class="swal2-input" placeholder="Destination">' +
                    '<input id="swalDate" type="date" class="swal2-input">' +
                    '<input id="swalNotes" class="swal2-input" placeholder="Notes">',
                confirmButtonText: 'Add',
                preConfirm: () => {
                    const start = document.getElementById('swalStart').value;
                    const dest = document.getElementById('swalDest').value;
                    if (! start || ! dest) { Swal.showValidationMessage('Start and Destination are required'); return false; }
                    return { start_point: start, destination: dest, leg_date: document.getElementById('swalDate').value, notes: document.getElementById('swalNotes').value };
                },
            }).then(result => { if (result.isConfirmed) { legs.push(result.value); renderLegs(); } });
        });

        $('#legsTable').on('click', '.js-remove-leg', function () { legs.splice($(this).data('i'), 1); renderLegs(); });

        $('#itineraryForm').on('submit', function (e) {
            e.preventDefault();
            $.post('{{ route("fleet.itineraries.store") }}', {
                clients: $('#clients').val(), destination: $('#destination').val(), start_point: $('#startPoint').val(),
                return_point: $('#returnPoint').val(), start_date: $('#startDate').val(), end_date: $('#endDate').val(),
                comments: $('#comments').val(), legs: legs,
            })
                .done(response => {
                    Swal.fire({ icon: 'success', title: 'Saved', timer: 1200, showConfirmButton: false }).then(() => {
                        window.location.href = response.can_approve
                            ? '{{ route("fleet.itineraries.approve") }}'
                            : '{{ route("fleet.itineraries.new") }}';
                    });
                })
                .fail(xhr => Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || 'Something went wrong.' }));
        });
    });
});
</script>
@endsection