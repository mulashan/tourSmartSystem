@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Previous Return</h2></div>

<table class="table table-hover">
    <thead><tr><th>S/N</th><th>Document No.</th><th>Store Returning</th><th>Store Receiving</th><th>Posted By</th><th>Received By</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($items as $i => $r)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $r->id }}</td>
                <td>{{ $r->fromSubdepartment->Subdepartment_Name ?? '—' }}</td>
                <td>{{ $r->toSubdepartment->Subdepartment_Name ?? '—' }}</td>
                <td>{{ $r->postedBy->name ?? '—' }}</td>
                <td>{{ $r->receivedBy->name ?? '—' }}</td>
                <td class="text-end">
                    <a href="{{ route('storage_supplies.return.preview', $r->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted">No completed returns yet.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection