<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Store Order - {{ $storeRequisition->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; padding: 30px; }
        .header { text-align: center; margin-bottom: 15px; }
        .header img { max-height: 80px; }
        .header h2 { margin: 5px 0; }
        .header .contact { font-size: 12px; line-height: 1.4; }
        .divider { border-top: 3px solid #e67e22; margin: 10px 0 20px; }
        .title { text-align: center; font-weight: bold; text-decoration: underline; margin-bottom: 20px; }
        .meta { margin-bottom: 15px; }
        .meta div { display: flex; margin-bottom: 3px; }
        .meta label { font-weight: bold; width: 160px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #999; padding: 6px 8px; font-size: 13px; text-align: left; }
        th { background: #f2f2f2; }
        .totals-row td { font-weight: bold; }
        .signature { margin-top: 40px; font-size: 13px; }
        .signature strong { display: block; }
        .footer { margin-top: 60px; font-size: 11px; color: #777; display: flex; justify-content: space-between; }
        .no-print { text-align: center; margin-bottom: 20px; }
        @media print {
            .no-print { display: none; }
        }
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
        <div><label>Order Number</label> : {{ $storeRequisition->id }}</div>
        <div><label>Order Date</label> : {{ $storeRequisition->order_date }}</div>
        <div><label>Store Ordering</label> : {{ $storeRequisition->subdepartment->Subdepartment_Name ?? '—' }}</div>
        <div><label>Order Description</label> : {{ $storeRequisition->order_description }}</div>
        <div><label>Created By</label> : {{ $storeRequisition->preparedBy->name ?? '—' }}</div>
    </div>

    @if($storeRequisition->procurement_status === 'rejected')
        <div style="color:#c0392b; font-weight:bold; margin-bottom:15px; border:1px solid #c0392b; padding:8px;">
            Rejected by Procurement — Reason: {{ $storeRequisition->rejection_reason }}
        </div>
    @endif

    <table>
        <thead>
            <tr><th colspan="8">ORDER ITEMS</th></tr>
            <tr>
                <th>S/N</th><th>Item Name</th><th>UoM</th><th>Units</th><th>Items per Unit</th><th>Quantity</th><th>Item Details</th><th>Status</th>
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
                    <td>
                        @if($line->procurement_status === 'rejected') <span style="color:#c0392b;">Rejected</span>
                        @elseif($line->procurement_status === 'ordered') <span style="color:#27ae60;">Ordered</span>
                        @else <span style="color:#888;">Pending</span> @endif
                    </td>
                </tr>
            @endforeach
            <tr class="totals-row">
                <td colspan="3">Totals</td>
                <td>{{ $totals['units'] }}</td>
                <td>{{ $totals['items_per_unit'] }}</td>
                <td>{{ $totals['quantity'] }}</td>
                <td></td>
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
        <span>Printed by {{ $printedByName }} at {{ now()->format('Y-m-d H:i:s') }}</span>
        <span>1 / 1</span>
    </div>
</body>
</html>