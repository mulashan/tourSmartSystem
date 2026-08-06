<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title>GRN Against Issue Note - {{ $grn->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; color:#222; padding:30px; }
        .header { text-align:center; margin-bottom:15px; }
        .header h2 { margin:5px 0; }
        .divider { border-top:3px solid #e67e22; margin:10px 0 20px; }
        .title { text-align:center; font-weight:bold; text-decoration:underline; margin-bottom:20px; }
        .meta div { display:flex; margin-bottom:3px; }
        .meta label { font-weight:bold; width:200px; }
        table { width:100%; border-collapse:collapse; margin-bottom:20px; }
        th, td { border:1px solid #999; padding:6px 8px; font-size:13px; text-align:left; }
        th { background:#f2f2f2; }
        .item-heading td { background:#f8f8f8; font-weight:bold; }
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
    <div class="title">GOODS RECEIVING NOTE — AGAINST ISSUE NOTE</div>

    <div class="meta">
        <div><label>GRN No.</label> : {{ $grn->id }}</div>
        <div><label>Issue Note No.</label> : {{ $grn->issue_note_id }}</div>
        <div><label>Store Issuing</label> : {{ $grn->issueNote->requisition->issuingSubdepartment->Subdepartment_Name ?? '—' }}</div>
        <div><label>Store Receiving</label> : {{ $grn->issueNote->requisition->requestingSubdepartment->Subdepartment_Name ?? '—' }}</div>
        <div><label>Receipt Date</label> : {{ $grn->receipt_date }}</div>
        <div><label>Created By</label> : {{ $grn->createdBy->name ?? '—' }}</div>
        @if($grn->status === 'approved')
            <div><label>Approved By</label> : {{ $grn->approvedBy->name ?? '—' }} on {{ $grn->approved_at }}</div>
        @endif
    </div>

    <table>
        <thead>
            <tr><th>Item</th><th>UoM</th><th>Quantity Received</th></tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($grn->items as $grnItem)
                @php $grandTotal += $grnItem->quantity; @endphp
                <tr>
                    <td>{{ $grnItem->item->product_name ?? '—' }}</td>
                    <td>{{ $grnItem->item->unitOfMeasure->name ?? '-' }}</td>
                    <td>{{ $grnItem->quantity }}</td>
                </tr>
            @endforeach
            <tr class="item-heading"><td colspan="2">Grand Total Quantity</td><td>{{ $grandTotal }}</td></tr>
        </tbody>
    </table>
</body>
</html>