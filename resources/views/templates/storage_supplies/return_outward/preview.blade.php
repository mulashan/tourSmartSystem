<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Return Outward - {{ $return->id }}</title>
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
    <div class="title">RETURN OUTWARD</div>

    <div class="meta">
        <div><label>Document Number</label> : {{ $return->id }}</div>
        <div><label>Transaction Date</label> : {{ $return->transaction_date }}</div>
        <div><label>Description</label> : {{ $return->description }}</div>
        <div><label>Posted By</label> : {{ $return->postedBy->name ?? '—' }}</div>
        <div><label>Store</label> : {{ $return->subdepartment->Subdepartment_Name ?? '—' }}</div>
        <div><label>Receiving Supplier</label> : {{ $return->supplier->supplier_name ?? '—' }}</div>
        <div><label>Status</label> : {{ ucwords(str_replace('_', ' ', $return->status)) }}</div>
        @if($return->approved_at)
            <div><label>Approved By</label> : {{ $return->approvedBy->name ?? '—' }} on {{ $return->approved_at }}</div>
        @endif
    </div>

    <table>
        <thead><tr><th>S/N</th><th>Item Name</th><th>UoM</th><th>Quantity</th></tr></thead>
        <tbody>
            @foreach($return->items as $i => $line)
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