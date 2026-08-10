@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Pending Receipt</h2></div>

<div class="table-responsive">
<table class="table table-hover" data-datatable data-export-name="pending-transfer-list" data-fixed-columns>
    <thead><tr><th>S/N</th><th>Transfer From</th><th>Transfer To</th><th>Created By</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($items as $i => $t)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $t->fromSubdepartment->Subdepartment_Name ?? '—' }}</td>
                <td>{{ $t->toSubdepartment->Subdepartment_Name ?? '—' }}</td>
                <td>{{ $t->createdBy->name ?? '—' }}</td>
                <td class="text-end">
                    @if($t->viewer_role === 'receiver')
                        <button type="button" class="btn btn-sm btn-success js-receive-transfer" data-id="{{ $t->id }}">Receive</button>
                    @endif
                    <a href="{{ route('storage_supplies.store_transfer.preview', $t->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                </td>
            </tr>
        @empty
        @endforelse
    </tbody>
</table>
</div>
@endsection

@section('scripts')
<script>
(function whenJQueryReady(fn) {
    if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); }
})(function () {
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        $('.js-receive-transfer').on('click', function () {
            const id = $(this).data('id');
            Swal.fire({ icon: 'warning', title: 'Confirm receipt of this transfer?', showCancelButton: true, confirmButtonText: 'Yes, receive it' }).then(result => {
                if (! result.isConfirmed) return;
                $.post(`/storage-supplies/store-transfer/${id}/receive`)
                    .done(() => Swal.fire({ icon: 'success', title: 'Received', timer: 1200, showConfirmButton: false }).then(() => location.reload()))
                    .fail(xhr => Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || 'Something went wrong.' }));
            });
        });
    });
});
</script>
@endsection