@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Approve Itineraries</h2></div>
<table class="table table-hover" data-datatable data-export-name="approve-itineraries">
    <thead><tr><th>Trip No.</th><th>Client(s)</th><th>Destination</th><th>Created By</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($itineraries as $i)
            <tr>
                <td>{{ $i->trip_number }}</td><td>{{ $i->clients }}</td><td>{{ $i->destination }}</td><td>{{ $i->createdBy->name ?? '—' }}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-warning js-approve" data-id="{{ $i->id }}">Approve</button>
                    <a href="{{ route('fleet.itineraries.preview', $i->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                </td>
            </tr>
        @empty
        @endforelse
    </tbody>
</table>

<div class="modal fade" id="approveModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form id="approveForm">
        <div class="modal-header"><h5 class="modal-title">Approve Itinerary</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" id="approveId">
            <div class="mb-3"><label class="form-label">Username</label><input type="text" id="approveUsername" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Password</label><input type="password" id="approvePassword" class="form-control" required></div>
            <div class="text-danger small" id="approveFormError"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Approve</button></div>
    </form>
</div></div></div>
@endsection

@section('scripts')
<script>
(function whenJQueryReady(fn) { if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); } })(function () {
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
        const modal = new bootstrap.Modal(document.getElementById('approveModal'));
        $('.js-approve').on('click', function () { $('#approveId').val($(this).data('id')); $('#approveForm')[0].reset(); $('#approveFormError').text(''); modal.show(); });

        $('#approveForm').on('submit', function (e) {
            e.preventDefault();
            $.post(`/fleet/itineraries/${$('#approveId').val()}/approve`, { username: $('#approveUsername').val(), password: $('#approvePassword').val() })
                .done(response => {
                    Swal.fire({ icon: 'success', title: 'Approved', timer: 1200, showConfirmButton: false }).then(() => {
                        window.location.href = response.can_assign
                            ? '{{ route("fleet.itineraries.assign") }}'
                            : '{{ url()->current() }}';
                    });
                })
                .fail(xhr => $('#approveFormError').text(xhr.responseJSON?.message || 'Approval failed.'));
        });
    });
});
</script>
@endsection