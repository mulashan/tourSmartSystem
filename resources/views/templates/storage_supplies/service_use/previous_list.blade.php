@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="settings-panel-head">
    <h2>Previous Service Use</h2>
    <a href="{{ route('storage_supplies.service_use.new') }}" class="btn btn-info text-white"><i class="bi bi-plus-lg"></i> New Service Use</a>
</div>

<div class="table-responsive">
<table class="table table-hover" data-datatable data-export-name="previous-service-use-list" data-fixed-columns>
    <thead><tr><th>S/N</th><th>Service Use No.</th><th>Service Use Date</th><th>Store</th><th>Service Use Narration</th><th>Created By</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($items as $i => $su)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $su->id }}</td>
                <td>{{ $su->requisition_date }}</td>
                <td>{{ $su->subdepartment->Subdepartment_Name ?? '—' }}</td>
                <td>{{ $su->reason }}</td>
                <td>{{ $su->officer->name ?? '—' }}</td>
                <td class="text-end">
                    <a href="{{ route('storage_supplies.service_use.preview', $su->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                </td>
            </tr>
        @empty
        @endforelse
    </tbody>
</table>
</div>
@endsection