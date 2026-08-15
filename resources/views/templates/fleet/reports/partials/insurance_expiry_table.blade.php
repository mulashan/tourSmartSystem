<div class="table-responsive">
    <table class="table table-hover" data-datatable data-export-name="insurance-expiry">
        <thead><tr><th>S/N</th><th>Vehicle</th><th>Insurance Type</th><th>Policy No.</th><th>Expiry Date</th><th>Days Left</th></tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
                @php $daysLeft = (int) round(now()->diffInDays($r->expire_date, false)); @endphp
                <tr class="{{ $daysLeft < 0 ? 'table-danger' : ($daysLeft <= 14 ? 'table-warning' : '') }}">
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r->vehicle->registration_no ?? '—' }}</td>
                    <td>{{ $r->insuranceType->name ?? '—' }}</td>
                    <td>{{ $r->policy_number }}</td>
                    <td>{{ $r->expire_date }}</td>
                    <td>{{ $daysLeft < 0 ? 'Expired' : $daysLeft . ' days' }}</td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>