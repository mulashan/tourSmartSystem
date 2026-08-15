<!doctype html>
<html>
<head>
    <meta charset="UTF-8"><title>Gate Pass - {{ $pass->gate_pass_no }}</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; }
        table { width:100%; border-collapse:collapse; } th, td { border:1px solid #999; padding:6px 8px; font-size:13px; text-align:left; }
        .meta div { display:flex; margin-bottom:3px; } .meta label { font-weight:bold; width:180px; }
        .no-print { text-align:center; margin-bottom:20px; } @media print { .no-print { display:none; } }
    </style>
</head>
<body>
    <div class="no-print"><button onclick="markPrintedAndPrint()">Print</button><button onclick="window.close()">Close</button></div>
    <h2 style="text-align:center;">{{ $company->Company_Name ?? '' }}</h2>
    <h3 style="text-align:center;text-decoration:underline;">GATE PASS — {{ $pass->gate_pass_no }}</h3>
    <div class="meta">
        <div><label>Vehicle</label> : {{ $pass->vehicle->registration_no ?? '—' }}</div>
        <div><label>Driver</label> : {{ $pass->driver->Employee_Name ?? '—' }}</div>
        <div><label>Destination</label> : {{ $pass->itinerary->destination ?? '—' }}</div>
        <div><label>Trip Number</label> : {{ $pass->itinerary->trip_number ?? '—' }}</div>
        <div><label>Date/Time Out</label> : {{ $pass->date_time_out }}</div>
        <div><label>Expected Return</label> : {{ $pass->expected_return ?? '—' }}</div>
        <div><label>Odometer Reading</label> : {{ $pass->odometer_reading ?? '—' }}</div>
        <div><label>Fuel Level</label> : {{ $pass->fuel_level ?? '—' }}</div>
        <div><label>Passengers/Tourists</label> : {{ $pass->passengers ?? '—' }}</div>
        <div><label>Authorized By</label> : {{ $pass->authorizedBy->name ?? '—' }}</div>
    </div>

    <script>
        function markPrintedAndPrint() {
            fetch('/fleet/gate-pass/{{ $pass->id }}/mark-printed', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
            }).finally(() => window.print());
        }
    </script>
</body>
</html>