{{-- partials/cancelled_purchase_requisition_table.blade.php --}}
<p class="text-muted small">Only requisitions rejected on or after this tracking was added will appear here — earlier rejections weren't attributed to a Procurement store.</p>
<div class="table-responsive">
    <table class="table table-hover" style="min-width:1400px;">
        <thead>
            <tr>
                <th>S/N</th><th>Store Order Requisition No.</th><th>Purchase Requisition No.</th><th>Status</th>
                <th>Cancelled Reason</th><th>Cancelled By</th><th>Store Requesting</th><th>Supplier</th>
                <th>Purchase Description</th><th>Created By</th><th class="text-end">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r->id }}</td>
                    <td>—</td>
                    <td><span class="text-danger">Rejected</span></td>
                    <td>{{ $r->rejection_reason }}</td>
                    <td>{{ $r->cancelledBy->name ?? '—' }}</td>
                    <td>{{ $r->subdepartment->Subdepartment_Name ?? '—' }}</td>
                    <td>—</td>
                    <td>{{ $r->order_description }}</td>
                    <td>{{ $r->preparedBy->name ?? '—' }}</td>
                    <td class="text-end">
                        <a href="{{ route('storage_supplies.store_ordering.preview', $r->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="11" class="text-center text-muted">No cancelled requisitions in this period.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>