<div class="table-responsive">
    <table class="table table-hover">
        <thead><tr><th>S/N</th><th>Item Name</th><th>Batch Number</th><th>UoM</th><th>Balance</th><th>Expiry Date</th><th>Days to Expiry</th></tr></thead>
        <tbody>
            @forelse($rows as $i => $b)
                @php $daysLeft = (int) round(now()->diffInDays($b->expiry_date, false)); @endphp
                <tr class="{{ $daysLeft < 0 ? 'table-danger' : ($daysLeft <= 30 ? 'table-warning' : '') }}">
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $b->item->product_name ?? '—' }}</td>
                    <td>{{ $b->batch_number }}</td>
                    <td>{{ $b->item->unitOfMeasure->name ?? '-' }}</td>
                    <td>{{ $b->quantity_remaining }}</td>
                    <td>{{ $b->expiry_date->toDateString() }}</td>
                    <td>{{ $daysLeft < 0 ? 'Expired' : $daysLeft . ' days' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted">No batches expiring in this window.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>