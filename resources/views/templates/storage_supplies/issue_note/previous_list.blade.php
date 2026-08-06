@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Previous Issues</h2></div>

<table class="table table-hover">
    <thead><tr><th>S/N</th><th>Requisition No.</th><th>Store Requesting</th><th>Officer</th><th>Approved By</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($items as $i => $note)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $note->requisition_id }}</td>
                <td>{{ $note->requisition->requestingSubdepartment->Subdepartment_Name ?? '—' }}</td>
                <td>{{ $note->officer->name ?? '—' }}</td>
                <td>{{ $note->approvedBy->name ?? '—' }}</td>
                <td class="text-end"><a href="{{ route('storage_supplies.issue_note.preview', $note->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a></td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">No approved Issue Notes yet.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection