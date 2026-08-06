@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Previous Requisition</h2></div>

<table class="table table-hover">
    <thead><tr><th>S/N</th><th>Store Issuing</th><th>Officer</th><th>Approved By</th><th>Approved At</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($items as $i => $req)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $req->issuingSubdepartment->Subdepartment_Name ?? '—' }}</td>
                <td>{{ $req->officer->name ?? '—' }}</td>
                <td>{{ $req->approvedBy->name ?? '—' }}</td>
                <td>{{ $req->approved_at }}</td>
                <td class="text-end">
                    <a href="{{ route('storage_supplies.requisition.preview', $req->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">No approved requisitions yet.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection