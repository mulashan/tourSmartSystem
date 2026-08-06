<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Store Transfer - {{ $transfer->id }}</title>
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
    <div class="title">STORE TRANSFER</div>

    <div class="meta">
        <div><label>Transfer No.</label> : {{ $transfer->id }}</div>
        <div><label>Transfer Date</label> : {{ $transfer->transfer_date }}</div>
        <div><label>Transfer From</label> : {{ $transfer->fromSubdepartment->Subdepartment_Name ?? '—' }}</div>
        <div><label>Transfer To</label> : {{ $transfer->toSubdepartment->Subdepartment_Name ?? '—' }}</div>
        <div><label>Description</label> : {{ $transfer->description }}</div>
        <div><label>Created By</label> : {{ $transfer->createdBy->name ?? '—' }}</div>
        <div><label>Status</label> : {{ ucwords(str_replace('_', ' ', $transfer->status)) }}</div>

        @if($transfer->status === 'cancelled')
            <div><label>Cancelled By</label> : {{ $transfer->cancelledBy->name ?? '—' }} on {{ $transfer->cancelled_at }}</div>
            <div><label>Cancel Reason</label> : {{ $transfer->cancel_reason }}</div>
        @else
            @if($transfer->approved_at)
                <div><label>Approved By</label> : {{ $transfer->approvedBy->name ?? '—' }} on {{ $transfer->approved_at }}</div>
            @endif
            @if($transfer->received_at)
                <div><label>Received By</label> : {{ $transfer->receivedBy->name ?? '—' }} on {{ $transfer->received_at }}</div>
            @elseif(in_array($transfer->status, ['pending_receipt']))
                <div><label>Received By</label> : <span style="color:#c0392b;">Not yet received</span></div>
            @endif
        @endif
    </div>

    <table>
        <thead><tr><th>S/N</th><th>Item Name</th><th>UoM</th><th>Quantity</th></tr></thead>
        <tbody>
            @foreach($transfer->items as $i => $line)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $line->item->product_name ?? '—' }}</td>
                    <td>{{ $line->item->unitOfMeasure->name ?? '-' }}</td>
                    <td>{{ $line->quantity }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>