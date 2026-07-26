<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Local Purchase Order - {{ $lpo->local_purchase_order_id }}</title>
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
    <div class="title">LOCAL PURCHASE ORDER</div>

    <div class="meta">
        <div><label>LPO No.</label> : {{ $lpo->local_purchase_order_id }}</div>
        <div><label>Order Date</label> : {{ $lpo->order_date }}</div>
        <div><label>Supplier</label> : {{ $lpo->supplier->supplier_name ?? '—' }}</div>
        <div><label>Requisition Description</label> : {{ $lpo->requisition_description }}</div>
        <div><label>Created By</label> : {{ $lpo->createdBy->name ?? '—' }}</div>
        <div><label>Approved By</label> : {{ $lpo->approvedBy->name ?? '—' }} on {{ $lpo->approved_at }}</div>
    </div>

    <table>
        <thead><tr><th>S/N</th><th>Item Name</th><th>UoM</th><th>Quantity</th><th>Unit Price</th><th>Total</th></tr></thead>
        <tbody>
            @foreach($lpo->items as $i => $line)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $line->item->product_name ?? '—' }}</td>
                    <td>{{ $line->item->unitOfMeasure->name ?? '-' }}</td>
                    <td>{{ $line->Quantity_Required }}</td>
                    <td>{{ number_format($line->Price, 2) }}</td>
                    <td>{{ number_format($line->Quantity_Required * $line->Price, 2) }}</td>
                </tr>
            @endforeach
            <tr class="totals"><td colspan="5">Items Total</td><td>{{ number_format($itemsTotal, 2) }}</td></tr>
            @if($lpo->vat_charges)
                <tr><td colspan="5">VAT</td><td>{{ number_format($lpo->vat_charges, 2) }}</td></tr>
            @endif
            @if($lpo->transport_charges)
                <tr><td colspan="5">Transport Charges</td><td>{{ number_format($lpo->transport_charges, 2) }}</td></tr>
            @endif
            @if($lpo->labor_charges)
                <tr><td colspan="5">Labour Charges</td><td>{{ number_format($lpo->labor_charges, 2) }}</td></tr>
            @endif
            @if($lpo->bank_charges)
                <tr><td colspan="5">Bank Charges</td><td>{{ number_format($lpo->bank_charges, 2) }}</td></tr>
            @endif
            @if($lpo->freight_charges)
                <tr><td colspan="5">Freight Charges</td><td>{{ number_format($lpo->freight_charges, 2) }}</td></tr>
            @endif
            @if($lpo->other_charges)
                <tr><td colspan="5">Other Charges</td><td>{{ number_format($lpo->other_charges, 2) }}</td></tr>
            @endif
            <tr class="totals"><td colspan="5">Grand Total ({{ $lpo->currency_type }})</td><td>{{ number_format($grandTotal, 2) }}</td></tr>
        </tbody>
    </table>
</body>
</html>