@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Previous GRN List — Against Issue Note</h2></div>

<div class="table-responsive">
<table class="table table-hover" data-datatable data-export-name="previous-GRN-against-issue-note" data-fixed-columns>
    <thead>
        <tr>
            <th>S/N</th><th>GRN #</th><th>Issue Note #</th><th>Store Issuing</th>
            <th>Created By</th><th>Approved By</th><th>Approved At</th><th class="text-end">Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $i => $grn)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $grn->id }}</td>
                <td>{{ $grn->issueNote->id ?? '—' }}</td>
                <td>{{ $grn->issueNote->requisition->issuingSubdepartment->Subdepartment_Name ?? '—' }}</td>
                <td>{{ $grn->createdBy->name ?? '—' }}</td>
                <td>{{ $grn->approvedBy->name ?? '—' }}</td>
                <td>{{ $grn->approved_at }}</td>
                <td class="text-end">
                    <a href="{{ route('storage_supplies.grn_against_issue_note.preview', $grn->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                </td>
            </tr>
        @empty
        @endforelse
    </tbody>
</table>
</div>
@endsection