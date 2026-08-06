@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Previous GRN List — Open Balance / Physical Count</h2></div>

<table class="table table-hover">
    <thead><tr><th>S/N</th><th>Creation Date</th><th>Description</th><th>Created By</th><th>Amount</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($items as $i => $grn)
            @php $amount = $grn->items->flatMap->batches->sum(fn ($b) => $b->quantity * $b->buying_price); @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $grn->creation_date }}</td>
                <td>{{ $grn->description }}</td>
                <td>{{ $grn->createdBy->name ?? '—' }}</td>
                <td>{{ number_format($amount, 2) }}</td>
                <td class="text-end">
                    <a href="{{ route('storage_supplies.grn_open_balance.preview', $grn->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">No approved entries yet.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection