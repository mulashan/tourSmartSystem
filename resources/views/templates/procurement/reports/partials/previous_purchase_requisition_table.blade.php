{{-- partials/previous_purchase_requisition_table.blade.php --}}
<div class="table-responsive">
    <table class="table table-hover" style="min-width:1300px;">
        <thead>
            <tr>
                <th>S/N</th><th>Store Requisition No.</th><th>Purchase Requisition No.</th><th>GRN No.</th>
                <th>Created Date</th><th>Store Requesting</th><th>Supplier</th><th>Purchase Description</th><th>Created By</th><th class="text-end">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $lpo)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $lpo->store_requisition_id }}</td>
                    <td>{{ $lpo->local_purchase_order_id }}</td>
                    <td>{{ $lpo->grn->Grn_Purchase_Order_ID ?? '—' }}</td>
                    <td>{{ $lpo->created_at->format('Y-m-d') }}</td>
                    <td>{{ $lpo->storeRequisition->subdepartment->Subdepartment_Name ?? '—' }}</td>
                    <td>{{ $lpo->supplier->supplier_name ?? '—' }}</td>
                    <td>{{ $lpo->requisition_description }}</td>
                    <td>{{ $lpo->createdBy->name ?? '—' }}</td>
                    <td class="text-end">
                        <a href="{{ route('procurement.store_requisitions.preview', $lpo->store_requisition_id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-center text-muted">No purchase requisitions in this period.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>