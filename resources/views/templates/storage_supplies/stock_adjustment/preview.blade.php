<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Stock Adjustment - {{ $adjustment->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; color:#222; padding:30px; }
        .header { text-align:center; margin-bottom:15px; }
        .header h2 { margin:5px 0; }
        .divider { border-top:3px solid #e67e22; margin:10px 0 20px; }
        .title { text-align:center; font-weight:bold; text-decoration:underline; margin-bottom:20px; }
        .meta div { display:flex; margin-bottom:3px; }
        .meta label { font-weight:bold; width:180px; }
        table { width:100%; border-collapse:collapse; margin-bottom:20px; }
        th, td { border:1px solid #999; padding:6px 8px; font-size:13px; text-align:left; }
        th { background:#f2f2f2; }
        .no-print { text-align:center; margin-bottom:20px; }
        @media print { .no-print { display:none; } }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()">Close</button>
    </div>

    <div class="header">
        @if($company?->Company_Logo)
            <img src="{{ asset($company->Company_Logo) }}" style="max-height:80px;"><br>
        @endif
        <h2>{{ $company->Company_Name ?? 'Company' }}</h2>
        <div>{{ $branch->Branch_Name ?? '' }}</div>
    </div>
    <div class="divider"></div>
    <div class="title">STOCK ADJUSTMENT</div>

    <div class="meta">
        <div><label>Adjustment Number</label> : {{ $adjustment->id }}</div>
        <div><label>Adjustment Date</label> : {{ $adjustment->adjustment_date }}</div>
        <div><label>Adjustment Officer</label> : {{ $adjustment->officer->name ?? '—' }}</div>
        <div><label>Adjustment Store</label> : {{ $adjustment->subdepartment->Subdepartment_Name ?? '—' }}</div>
        <div><label>Description</label> : {{ $adjustment->description }}</div>
        <div><label>Reason</label> : {{ $adjustment->reason === 'add_stock_balance' ? 'Add Stock Balance' : 'Expired / Dump / Broken' }}</div>
        @if($adjustment->approved_at)
            <div><label>Approved By</label> : {{ $adjustment->approvedBy->name ?? '—' }} on {{ $adjustment->approved_at }}</div>
        @endif
    </div>

    @if($adjustment->reason === 'add_stock_balance')
        <table>
            <thead><tr><th>Item</th><th>Batch No.</th><th>Units</th><th>Items per Unit</th><th>Quantity</th><th>Buying Price</th><th>Amount</th></tr></thead>
            <tbody>
                @php $grandTotal = 0; @endphp
                @foreach($adjustment->items as $item)
                    @foreach($item->batches as $batch)
                        @php $lineTotal = $batch->quantity * $batch->buying_price; $grandTotal += $lineTotal; @endphp
                        <tr>
                            <td>{{ $item->item->product_name ?? '—' }}</td>
                            <td>{{ $batch->batch_number }}</td>
                            <td>{{ $batch->units }}</td>
                            <td>{{ $batch->items_per_unit }}</td>
                            <td>{{ $batch->quantity }}</td>
                            <td>{{ number_format($batch->buying_price, 2) }}</td>
                            <td>{{ number_format($lineTotal, 2) }}</td>
                        </tr>
                    @endforeach
                @endforeach
                <tr><td colspan="6"><strong>Grand Total</strong></td><td><strong>{{ number_format($grandTotal, 2) }}</strong></td></tr>
            </tbody>
        </table>
    @else
        <table>
            <thead><tr><th>S/N</th><th>Item Name</th><th>UoM</th><th>Quantity</th></tr></thead>
            <tbody>
                @foreach($adjustment->items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item->item->product_name ?? '—' }}</td>
                        <td>{{ $item->item->unitOfMeasure->name ?? '-' }}</td>
                        <td>{{ $item->quantity }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>