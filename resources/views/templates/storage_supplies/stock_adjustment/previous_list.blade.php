@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Previous Adjustments</h2></div>

<div class="table-responsive">
<table class="table table-hover" data-datatable data-export-name="previous-adjustment-list" data-fixed-columns>
    <thead><tr><th>S/N</th><th>Adjustment No.</th><th>Reason</th><th>Officer</th><th>Approved By</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($items as $i => $adj)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $adj->id }}</td>
                <td>{{ $adj->reason === 'add_stock_balance' ? 'Add Stock Balance' : 'Expired / Dump / Broken' }}</td>
                <td>{{ $adj->officer->name ?? '—' }}</td>
                <td>{{ $adj->approvedBy->name ?? '—' }}</td>
                <td class="text-end">
                    <a href="{{ route('storage_supplies.stock_adjustment.preview', $adj->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                </td>
            </tr>
        @empty
        @endforelse
    </tbody>
</table>
</div>
@endsection
