<div class="table-responsive">
    <table class="table table-hover" style="min-width:1100px;">
        <thead>
            <tr><th>S/N</th><th>Doc No.</th><th>Date</th><th>Narration</th><th>Received</th><th>Issued</th><th>Adjustment</th><th>Reason</th><th>Employee</th></tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r->reference_id }}</td>
                    <td>{{ $r->moved_at }}</td>
                    <td>{{ $narrations[$r->movement_type] ?? ucfirst(str_replace('_', ' ', $r->movement_type)) }}</td>
                    <td>{{ $r->quantity_in ?: '' }}</td>
                    <td>{{ $r->quantity_out ?: '' }}</td>
                    <td>{{ in_array($r->movement_type, ['adjustment_add', 'adjustment_deduct']) ? ($r->quantity_in ?: $r->quantity_out) : '' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $r->reference_type)) }}</td>
                    <td>{{ $r->createdBy->name ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted">No movements in this period.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>