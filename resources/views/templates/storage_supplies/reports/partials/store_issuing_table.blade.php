<div class="table-responsive">
<table class="table table-hover" data-datatable data-export-name="batch-management-report" data-fixed-columns>
        <thead>
            <tr>
                <th>S/N</th><th>Item Name</th><th>Store Requesting</th><th>Store Issued</th><th>Requisition No.</th><th>Issue No.</th>
                <th>UoM</th><th>Qty Requested</th><th>Qty Issued</th><th>Issue Date</th><th>Issued By</th><th>Receiving Officer</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r['item']->product_name ?? '—' }}</td>
                    <td>{{ $r['requesting'] }}</td>
                    <td>{{ $r['issuing'] }}</td>
                    <td>{{ $r['requisition_no'] }}</td>
                    <td>{{ $r['issue_no'] }}</td>
                    <td>{{ $r['item']->unitOfMeasure->name ?? '-' }}</td>
                    <td>{{ $r['quantity_requested'] }}</td>
                    <td>{{ $r['quantity_issued'] }}</td>
                    <td>{{ $r['issue_date'] }}</td>
                    <td>{{ $r['issued_by'] }}</td>
                    <td>{{ $r['receiving_officer'] }}</td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>