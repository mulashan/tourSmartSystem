@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>New Issue Note — Approved Requisitions Awaiting Issue</h2></div>

<table class="table table-hover">
    <thead><tr><th>S/N</th><th>Requisition No.</th><th>Store Requesting</th><th>Officer</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($items as $i => $req)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $req->id }}</td>
                <td>{{ $req->requestingSubdepartment->Subdepartment_Name ?? '—' }}</td>
                <td>{{ $req->officer->name ?? '—' }}</td>
                <td class="text-end">
                    <a href="{{ route('storage_supplies.issue_note.create', $req->id) }}" class="btn btn-sm btn-info text-white">Process</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted">No approved requisitions awaiting an Issue Note.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection