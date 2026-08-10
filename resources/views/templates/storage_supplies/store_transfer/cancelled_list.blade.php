@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Cancelled Transfers</h2></div>

<div class="table-responsive">
<table class="table table-hover" data-datatable data-export-name="cancelled-transfer-list" data-fixed-columns>
    <thead><tr><th>S/N</th><th>Transfer To</th><th>Created By</th><th>Cancelled By</th><th>Reason</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($items as $i => $t)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $t->toSubdepartment->Subdepartment_Name ?? '—' }}</td>
                <td>{{ $t->createdBy->name ?? '—' }}</td>
                <td>{{ $t->cancelledBy->name ?? '—' }}</td>
                <td>{{ $t->cancel_reason }}</td>
                <td class="text-end">
                    <a href="{{ route('storage_supplies.store_transfer.preview', $t->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                </td>
            </tr>
        @empty
        @endforelse
    </tbody>
</table>
</div>
@endsection