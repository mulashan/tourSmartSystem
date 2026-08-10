@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>New GRN — Approved Issue Notes Awaiting Receipt</h2></div>

<div class="table-responsive">
<table class="table table-hover" data-datatable data-export-name="new-GRN-against-issue-list" data-fixed-columns>
    <thead><tr><th>S/N</th><th>Issue Note No.</th><th>Store Issuing</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($items as $i => $note)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $note->id }}</td>
                <td>{{ $note->requisition->issuingSubdepartment->Subdepartment_Name ?? '—' }}</td>
                <td class="text-end"><a href="{{ route('storage_supplies.grn_against_issue_note.create', $note->id) }}" class="btn btn-sm btn-info text-white">Process</a></td>
            </tr>
        @empty
        @endforelse
    </tbody>
</table>
</div>
@endsection
