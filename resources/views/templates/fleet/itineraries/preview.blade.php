<!doctype html>
<html>
<head>
    <meta charset="UTF-8"><title>Itinerary - {{ $itinerary->trip_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; }
        table { width:100%; border-collapse:collapse; margin-bottom:20px; }
        th, td { border:1px solid #999; padding:6px 8px; font-size:13px; text-align:left; }
        th { background:#f2f2f2; }
        .meta div { display:flex; margin-bottom:3px; } .meta label { font-weight:bold; width:180px; }
        .no-print { text-align:center; margin-bottom:20px; } @media print { .no-print { display:none; } }
    </style>
</head>
<body>
    <div class="no-print"><button onclick="window.print()">Print</button><button onclick="window.close()">Close</button></div>
    <h2 style="text-align:center;">{{ $company->Company_Name ?? '' }}</h2>
    <h3 style="text-align:center;text-decoration:underline;">ITINERARY — {{ $itinerary->trip_number }}</h3>
    <div class="meta">
        <div><label>Client(s)</label> : {{ $itinerary->clients }}</div>
        <div><label>Start</label> : {{ $itinerary->start_point }}</div>
        <div><label>Destination</label> : {{ $itinerary->destination }}</div>
        <div><label>Return</label> : {{ $itinerary->return_point }}</div>
        <div><label>Dates</label> : {{ $itinerary->start_date }} to {{ $itinerary->end_date }}</div>
        <div><label>Vehicle</label> : {{ $itinerary->vehicle->registration_no ?? 'Not yet assigned' }}</div>
        <div><label>Driver</label> : {{ $itinerary->driver->Employee_Name ?? 'Not yet assigned' }}</div>
        <div><label>Status</label> : {{ ucwords(str_replace('_', ' ', $itinerary->status)) }}</div>
        <div><label>Comments</label> : {{ $itinerary->comments }}</div>
    </div>
    @if($itinerary->legs->isNotEmpty())
        <table><thead><tr><th>Leg</th><th>Start</th><th>Destination</th><th>Date</th><th>Notes</th></tr></thead>
        <tbody>@foreach($itinerary->legs as $leg)<tr><td>{{ $leg->leg_number }}</td><td>{{ $leg->start_point }}</td><td>{{ $leg->destination }}</td><td>{{ $leg->leg_date }}</td><td>{{ $leg->notes }}</td></tr>@endforeach</tbody></table>
    @endif
</body>
</html>