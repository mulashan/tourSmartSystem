<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Purchase Order Requisition - {{ $storeRequisition->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; padding: 30px; }
        .header { text-align: center; margin-bottom: 15px; }
        .header img { max-height: 80px; }
        .header h2 { margin: 5px 0; }
        .header .contact { font-size: 12px; line-height: 1.4; }
        .divider { border-top: 3px solid #e67e22; margin: 10px 0 20px; }
        .title { text-align: center; font-weight: bold; text-decoration: underline; margin-bottom: 20px; }
        .meta div { display: flex; margin-bottom: 3px; }
        .meta label { font-weight: bold; width: 180px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #999; padding: 6px 8px; font-size: 13px; text-align: left; }
        th { background: #f2f2f2; }
        .totals-row td { font-weight: bold; }
        .signature { margin-top: 40px; font-size: 13px; }
        .signature strong { display: block; }
        .footer { margin-top: 60px; font-size: 11px; color: #777; display: flex; justify-content: space-between; }
        .no-print { text-align: center; margin-bottom: 20px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()">Close</button>
    </div>

    <div class="header">
        @if($company?->Company_Logo)
            <img src="{{ asset($company->Company_Logo) }}" alt="Logo"><br>
        @endif
        <h2>{{ $company->Company_Name ?? 'Company' }}</h2>
        <div class="contact">
            @if($branch)
                {{ $branch->Branch_Name }}
                @if($branch->Location) — {{ $branch->Location }} @endif
            @endif
        </div>
    </div>
    <div class="divider"></div>

    <div class="title">PURCHASE ORDER REQUISITION</div>

    <div class="meta">
        <div><label>Requisition No.</label> : {{ $storeRequisition->id }}</div>
        <div><label>Requisition Date</label> : {{ $storeRequisition->order_date }}</div>
        <div><label>Requisition Description</label> : {{ $storeRequisition->order_description }}</div>
        <div><label>Created By</label> : {{ $storeRequisition->preparedBy->name ?? '—' }}</div>
        @if($supplier)
            <div><label>Supplier</label> : {{ $supplier->supplier_name }}</div>
        @endif
    </div>

    <table>
        <thead>
            <tr><th colspan="7">REQUISITION ITEMS</th></tr>
            <tr>
                <th>S/N</th><th>Item Name</th><th>UoM</th><th>Units</th><th>Items per Unit</th><th>Quantity</th><th>Item Details</th>
            </tr>
        </thead>
        <tbody>
            @foreach($storeRequisition->items as $i => $line)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $line->item->product_name ?? '—' }}</td>
                    <td>{{ $line->item->unitOfMeasure->name ?? '-' }}</td>
                    <td>{{ $line->units }}</td>
                    <td>{{ $line->items_per_unit }}</td>
                    <td>{{ $line->quantity }}</td>
                    <td>{{ $line->item_details }}</td>
                </tr>
            @endforeach
            <tr class="totals-row">
                <td colspan="3">Totals</td>
                <td>{{ $totals['units'] }}</td>
                <td>{{ $totals['items_per_unit'] }}</td>
                <td>{{ $totals['quantity'] }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="signature">
        <strong>Prepared By</strong>
        <strong>{{ $storeRequisition->preparedBy->name ?? '—' }}</strong>
        <em>{{ $storeRequisition->created_at }}</em>
        <strong>{{ $preparedByTitle }}</strong>
    </div>

    <div class="footer">
        <span>Printed by {{ session('user_name') }} at {{ now()->format('Y-m-d H:i:s') }}</span>
        <span>1 / 1</span>
    </div>
</body>
</html>