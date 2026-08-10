@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Previous Return</h2></div>

<div class="table-responsive">
<table class="table table-hover" data-datatable data-export-name="previous-issue-list" data-fixed-columns>
    <thead><tr><th>S/N</th><th>Document No.</th><th>Description</th><th>Supplier</th><th>Posted By</th><th>Approved By</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($items as $i => $r)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $r->id }}</td>
                <td>{{ $r->description }}</td>
                <td>{{ $r->supplier->supplier_name ?? '—' }}</td>
                <td>{{ $r->postedBy->name ?? '—' }}</td>
                <td>{{ $r->approvedBy->name ?? '—' }}</td>
                <td class="text-end">
                    <a href="{{ route('storage_supplies.return_outward.preview', $r->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                </td>
            </tr>
        @empty
        @endforelse
    </tbody>
</table>
</div>
@endsection