<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gate Pass - {{ $pass->gate_pass_no }}</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; padding: 40px; color: #222; }
        .pass-card { max-width: 700px; margin: 0 auto; border: 2px solid #012970; border-radius: 12px; overflow: hidden; }
        .pass-header { background: #012970; color: #fff; text-align: center; padding: 20px; }
        .pass-header h1 { margin: 0; font-size: 20px; letter-spacing: 1px; }
        .pass-header h2 { margin: 6px 0 0; font-size: 28px; font-weight: 700; letter-spacing: 2px; }
        .pass-subtitle { text-align: center; padding: 14px; background: #f5f8ff; font-size: 15px; font-weight: 600; letter-spacing: 3px; color: #012970; }
        .pass-body { padding: 24px 30px; }
        .pass-row { display: flex; border-bottom: 1px dashed #ddd; padding: 10px 0; }
        .pass-row:last-child { border-bottom: none; }
        .pass-label { flex: 0 0 220px; font-weight: 600; color: #555; text-align: center; }
        .pass-value { flex: 1; text-align: center; font-weight: 600; }
        .pass-footer { text-align: center; padding: 18px; background: #f5f8ff; font-size: 12px; color: #777; }
        .no-print { text-align: center; margin-bottom: 25px; }
        .no-print button { padding: 8px 20px; margin: 0 5px; border-radius: 6px; border: 1px solid #ccc; cursor: pointer; background: #fff; }
        @media print { .no-print { display: none; } body { padding: 0; } .pass-card { border: 1px solid #000; } }
    </style>
</head>
<body>
    <div class="no-print"><button onclick="markPrintedAndPrint()">Print</button><button onclick="closeWindow()">Close</button></div>

    <div class="pass-card">
        <div class="pass-header">
            <h1>{{ $company->Company_Name ?? '' }}</h1>
            <h2>GATE PASS</h2>
        </div>
        <div class="pass-subtitle">{{ $pass->gate_pass_no }}</div>
        <div class="pass-body">
            <div class="pass-row"><div class="pass-label">Vehicle</div><div class="pass-value">{{ $pass->vehicle->registration_no ?? '—' }}</div></div>
            <div class="pass-row"><div class="pass-label">Driver</div><div class="pass-value">{{ $pass->driver->Employee_Name ?? '—' }}</div></div>
            <div class="pass-row"><div class="pass-label">Trip Number</div><div class="pass-value">{{ $pass->itinerary->trip_number ?? '—' }}</div></div>
            <div class="pass-row"><div class="pass-label">Destination</div><div class="pass-value">{{ $pass->itinerary->destination ?? '—' }}</div></div>
            <div class="pass-row"><div class="pass-label">Date/Time Out</div><div class="pass-value">{{ $pass->date_time_out }}</div></div>
            <div class="pass-row"><div class="pass-label">Expected Return</div><div class="pass-value">{{ $pass->expected_return ?? '—' }}</div></div>
            <div class="pass-row"><div class="pass-label">Odometer Reading</div><div class="pass-value">{{ $pass->odometer_reading ?? '—' }}</div></div>
            <div class="pass-row"><div class="pass-label">Fuel Level</div><div class="pass-value">{{ $pass->fuel_level ?? '—' }}</div></div>
            <div class="pass-row"><div class="pass-label">Passengers/Tourists</div><div class="pass-value">{{ $pass->passengers ?? '—' }}</div></div>
            <div class="pass-row"><div class="pass-label">Authorized By</div><div class="pass-value">{{ $pass->authorizedBy->name ?? '—' }}</div></div>
        </div>
        <div class="pass-footer">Generated on {{ $pass->created_at }}</div>
    </div>

    <script>
        function markPrintedAndPrint() {
            fetch('/fleet/gate-pass/{{ $pass->id }}/mark-printed', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            })
                .then(response => { if (! response.ok) throw new Error('Failed to record print status'); })
                .catch(err => console.error(err))
                .finally(() => { refreshOpener(); window.print(); });
        }
        function closeWindow() {
            refreshOpener();
            window.close();
            setTimeout(() => {
                if (! window.closed) {
                    document.body.insertAdjacentHTML('afterbegin', '<div style="background:#fff3cd;padding:10px;text-align:center;">You can close this tab now.</div>');
                }
            }, 200);
        }
        function refreshOpener() {
            if (window.opener && ! window.opener.closed) window.opener.location.reload();
        }
    </script>
</body>
</html>