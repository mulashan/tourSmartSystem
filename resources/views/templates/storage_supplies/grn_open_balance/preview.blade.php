<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title>GRN Without PO - {{ $grn->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; color:#222; padding:30px; }
        .header { text-align:center; margin-bottom:15px; }
        .header h2 { margin:5px 0; }
        .divider { border-top:3px solid #e67e22; margin:10px 0 20px; }
        .title { text-align:center; font-weight:bold; text-decoration:underline; margin-bottom:20px; }
        .meta div { display:flex; margin-bottom:3px; }
        .meta label { font-weight:bold; width:200px; }
        table { width:100%; border-collapse:collapse; margin-bottom:20px; }
        th, td { border:1px solid #999; padding:6px 8px; font-size:12px; text-align:left; }
        th { background:#f2f2f2; }
        .totals td { font-weight:bold; }
        .no-print { text-align:center; margin-bottom:20px; }
        @media print { .no-print { display:none; } }
    </style>
</head>
<body>
    <div class="no-print"><button onclick="window.print()">Print</button><button onclick="window.close()">Close</button></div>

    <div class="header">
        @if($company?->Company_Logo)<img src="{{ asset($company->Company_Logo) }}" style="max-height:80px;"><br>@endif
        <h2>{{ $company->Company_Name ?? 'Company' }}</h2>
        <div>{{ $branch->Branch_Name ?? '' }}</div>
    </div>
    <div class="divider"></div>
    <div class="title">OPEN BALANCE / PHYSICAL COUNT</div>

    <div class="meta">
        <div><label>GRN No.</label> : {{ $grn->id }}</div>
        <div><label>Purchase Description</label> : {{ $grn->purchase_description }}</div>
        <div><label>Delivery Date</label> : {{ $grn->delivery_date }}</div>
        <div><label>Created By</label> : {{ $grn->createdBy->name ?? '—' }}</div>
        <div><label>Approved By</label> : {{ $grn->approvedBy->name ?? '—' }} on {{ $grn->approved_at }}</div>
    </div>

    <table>
        <thead>
            <tr><th>Item</th><th>Batch No.</th><th>Units</th><th>Items per Unit</th><th>Quantity</th><th>Buying Price</th><th>Amount</th><th>Mfg. Date</th><th>Expiry Date</th><th>Remarks</th></tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($grn->items as $grnItem)
                @foreach($grnItem->batches as $batch)
                    @php $lineTotal = $batch->quantity * $batch->buying_price; $grandTotal += $lineTotal; @endphp
                    <tr>
                        <td>{{ $grnItem->item->product_name ?? '—' }}</td>
                        <td>{{ $batch->batch_number }}</td>
                        <td>{{ $batch->units }}</td>
                        <td>{{ $batch->items_per_unit }}</td>
                        <td>{{ $batch->quantity }}</td>
                        <td>{{ number_format($batch->buying_price, 2) }}</td>
                        <td>{{ number_format($lineTotal, 2) }}</td>
                        <td>{{ $batch->manufacture_date }}</td>
                        <td>{{ $batch->expiry_date }}</td>
                        <td>{{ $grnItem->remarks }}</td>
                    </tr>
                @endforeach
            @endforeach
            <tr class="totals"><td colspan="6">Grand Total</td><td>{{ number_format($grandTotal, 2) }}</td><td colspan="3"></td></tr>
        </tbody>
    </table>
</body>
</html>