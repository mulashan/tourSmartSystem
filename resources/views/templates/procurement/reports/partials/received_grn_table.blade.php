{{-- partials/received_grn_table.blade.php --}}
<div class="table-responsive">
    <table class="table table-hover" data-datatable data-export-name="received-GRN-report" data-fixed-columns>
        <thead>
            <tr>
                <th>S/N</th><th>LPO No.</th><th>Order No.</th><th>Order Created By</th><th>Delivery Date</th>
                <th>Store Requesting</th><th>Supplier</th><th>Delivery Note No.</th><th>Invoice No.</th>
                <th>Created By</th><th>Amount</th><th class="text-end">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r['lpo_no'] }}</td>
                    <td>{{ $r['order_no'] }}</td>
                    <td>{{ $r['order_created_by'] }}</td>
                    <td>{{ $r['delivery_date'] }}</td>
                    <td>{{ $r['store_requesting'] }}</td>
                    <td>{{ $r['supplier'] }}</td>
                    <td>{{ $r['delivery_note'] }}</td>
                    <td>{{ $r['invoice_no'] }}</td>
                    <td>{{ $r['created_by'] }}</td>
                    <td>{{ number_format($r['amount'], 2) }}</td>
                    <td class="text-end"><a href="{{ $r['preview_url'] }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a></td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>