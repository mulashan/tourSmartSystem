@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Return List</h2></div>

<table class="table table-hover">
    <thead><tr><th>S/N</th><th>Document No.</th><th>Store Returning</th><th>Store Receiving</th><th>Posted By</th><th>Status</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($items as $i => $r)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $r->id }}</td>
                <td>{{ $r->fromSubdepartment->Subdepartment_Name ?? '—' }}</td>
                <td>{{ $r->toSubdepartment->Subdepartment_Name ?? '—' }}</td>
                <td>{{ $r->postedBy->name ?? '—' }}</td>
                <td>
                    @if($r->viewer_role === 'sender')
                        <span class="text-muted">Not Received</span>
                    @else
                        <span class="text-warning">Awaiting Your Receipt</span>
                    @endif
                </td>
                <td class="text-end">
                    @if($r->viewer_role === 'receiver')
                        <button type="button" class="btn btn-sm btn-success js-receive-return" data-id="{{ $r->id }}">Receive</button>
                    @endif
                    <a href="{{ route('storage_supplies.return.preview', $r->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted">No returns pending receipt.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection

@section('scripts')
<script>
(function whenJQueryReady(fn) {
    if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); }
})(function () {
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        $('.js-receive-return').on('click', function () {
            const id = $(this).data('id');

            Swal.fire({ icon: 'warning', title: 'Confirm receipt of this return?', showCancelButton: true, confirmButtonText: 'Yes, receive it' }).then(result => {
                if (! result.isConfirmed) return;

                $.post(`/storage-supplies/return/${id}/receive`)
                    .done(() => Swal.fire({ icon: 'success', title: 'Received', timer: 1200, showConfirmButton: false }).then(() => location.reload()))
                    .fail(xhr => Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || 'Something went wrong.' }));
            });
        });
    });
});
</script>
@endsection