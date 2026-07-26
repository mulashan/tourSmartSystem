@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Previous GRN List</h2></div>

<div class="table-responsive">
    <table class="table table-hover" style="min-width:1300px;">
        <thead>
            <tr>
                <th>S/N</th><th>LPO No.</th><th>Order No.</th><th>Order Created By</th><th>Delivery Date</th>
                <th>Store Requesting</th><th>Delivery Note Number</th><th>Invoice Number</th>
                <th>Created By</th><th>Amount</th><th class="text-end">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $i => $grn)
                @php
                    $amount = $grn->items->flatMap->batches->sum(fn ($b) => $b->quantity * $b->buying_price);
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $grn->local_purchase_order_id }}</td>
                    <td>{{ $grn->localPurchaseOrder->store_requisition_id ?? '—' }}</td>
                    <td>{{ $grn->localPurchaseOrder->createdBy->name ?? '—' }}</td>
                    <td>{{ $grn->Delivery_Date }}</td>
                    <td>{{ $grn->subdepartment->Subdepartment_Name ?? '—' }}</td>
                    <td>{{ $grn->Delivery_Note_Number }}</td>
                    <td>{{ $grn->Invoice_Number }}</td>
                    <td>{{ $grn->createdBy->name ?? '—' }}</td>
                    <td>{{ number_format($amount, 2) }}</td>
                    <td class="text-end">
                        <a href="{{ route('storage_supplies.grn.preview', $grn->Grn_Purchase_Order_ID) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="11" class="text-center text-muted">No approved GRNs yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection