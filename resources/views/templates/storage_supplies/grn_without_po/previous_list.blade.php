@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Previous GRN List — Without Purchase Order</h2></div>

<div class="table-responsive">
    <table class="table table-hover" style="min-width:1100px;" data-datatable data-export-name="Previous-orders-without-PO" data-fixed-columns>
        <thead>
            <tr>
                <th>S/N</th><th>Delivery Date</th><th>Store Requesting</th><th>Delivery Note Number</th>
                <th>Invoice Number</th><th>Created By</th><th>Amount</th><th class="text-end">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $i => $grn)
                @php $amount = $grn->items->flatMap->batches->sum(fn ($b) => $b->quantity * $b->buying_price); @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $grn->delivery_date }}</td>
                    <td>{{ $grn->subdepartment->Subdepartment_Name ?? '—' }}</td>
                    <td>{{ $grn->delivery_note_number }}</td>
                    <td>{{ $grn->invoice_number }}</td>
                    <td>{{ $grn->createdBy->name ?? '—' }}</td>
                    <td>{{ number_format($amount, 2) }}</td>
                    <td class="text-end">
                        <a href="{{ route('storage_supplies.grn_without_po.preview', $grn->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                    </td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>
@endsection