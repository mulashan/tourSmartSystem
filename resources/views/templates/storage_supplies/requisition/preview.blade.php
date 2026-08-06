<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Requisition - {{ $requisition->id }}</title>
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
    <div class="no-print"><button onclick="window.print()">Print</button><button onclick="window.close()">Close</button></div>

    <div class="header">
        @if($company?->Company_Logo)<img src="{{ asset($company->Company_Logo) }}" style="max-height:80px;"><br>@endif
        <h2>{{ $company->Company_Name ?? 'Company' }}</h2>
        <div>{{ $branch->Branch_Name ?? '' }}</div>
    </div>
    <div class="divider"></div>
    <div class="title">STORE REQUISITION</div>

    <div class="meta">
        <div><label>Document Number</label> : {{ $requisition->id }}</div>
        <div><label>Requisition Date</label> : {{ $requisition->requisition_date }}</div>
        <div><label>Officer</label> : {{ $requisition->officer->name ?? '—' }}</div>
        <div><label>Store Requesting</label> : {{ $requisition->requestingSubdepartment->Subdepartment_Name ?? '—' }}</div>
        <div><label>Store Issuing</label> : {{ $requisition->issuingSubdepartment->Subdepartment_Name ?? '—' }}</div>
        <div><label>Description</label> : {{ $requisition->description }}</div>
        @if($requisition->status === 'approved')
            <div><label>Approved By</label> : {{ $requisition->approvedBy->name ?? '—' }} on {{ $requisition->approved_at }}</div>
        @endif
    </div>

    <table>
        <thead><tr><th>S/N</th><th>Item Name</th><th>UoM</th><th>Quantity Requested</th><th>Item Details</th></tr></thead>
        <tbody>
            @foreach($requisition->items as $i => $line)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $line->item->product_name ?? '—' }}</td>
                    <td>{{ $line->item->unitOfMeasure->name ?? '-' }}</td>
                    <td>{{ $line->quantity_requested }}</td>
                    <td>{{ $line->item_details }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>