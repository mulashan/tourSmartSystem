<div class="table-responsive">
    <table class="table table-hover" data-datatable data-export-name="stock-ledger" style="min-width:1100px;">
        <thead>
            <tr>
                <th>S/N</th><th>Doc No.</th><th>Date</th><th>Narration</th>
                <th>Increase</th><th>Decrease</th><th>Store Balance</th><th>Reason</th><th>Employee</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r->reference_id }}</td>
                    <td>{{ $r->moved_at }}</td>
                    <td>{{ $narrations[$r->movement_type] ?? ucfirst(str_replace('_', ' ', $r->movement_type)) }}</td>
                    <td class="text-success">{{ $r->quantity_in ? '+' . $r->quantity_in : '' }}</td>
                    <td class="text-danger">{{ $r->quantity_out ? '-' . $r->quantity_out : '' }}</td>
                    <td><strong>{{ $r->balance_after }}</strong></td>
                    <td>{{ ucfirst(str_replace('_', ' ', $r->reference_type)) }}</td>
                    <td>{{ $r->createdBy->name ?? '—' }}</td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>