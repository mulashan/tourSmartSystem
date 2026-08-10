@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Completed Transfers</h2></div>

<div class="table-responsive">
<table class="table table-hover" data-datatable data-export-name="completed-transfer-list" data-fixed-columns>
    <thead><tr><th>S/N</th><th>Transfer From</th><th>Transfer To</th><th>Created By</th><th>Received By</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($items as $i => $t)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $t->fromSubdepartment->Subdepartment_Name ?? '—' }}</td>
                <td>{{ $t->toSubdepartment->Subdepartment_Name ?? '—' }}</td>
                <td>{{ $t->createdBy->name ?? '—' }}</td>
                <td>{{ $t->receivedBy->name ?? '—' }}</td>
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
