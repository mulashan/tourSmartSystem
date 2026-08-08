{{-- partials/pending_po_aging_table.blade.php --}}
<div class="table-responsive">
    <table class="table table-hover">
        <thead><tr><th>S/N</th><th>LPO No.</th><th>Store Requesting</th><th>Supplier</th><th>Status</th><th>Started</th><th>Days Pending</th><th>Created By</th></tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr class="{{ $r['days'] > 7 ? 'table-danger' : ($r['days'] > 3 ? 'table-warning' : '') }}">
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r['lpo']->local_purchase_order_id }}</td>
                    <td>{{ $r['lpo']->storeRequisition->subdepartment->Subdepartment_Name ?? '—' }}</td>
                    <td>{{ $r['lpo']->supplier->supplier_name ?? '—' }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $r['lpo']->status)) }}</td>
                    <td>{{ $r['started_at'] }}</td>
                    <td>{{ $r['days'] }}</td>
                    <td>{{ $r['lpo']->createdBy->name ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted">No Purchase Orders currently pending.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>