<div class="card mb-3">
    <div class="card-body">
        <strong>Item Name:</strong> {{ $item->product_name }} &nbsp;&nbsp; <strong>UoM:</strong> {{ $item->unitOfMeasure->name ?? '-' }}
    </div>
</div>

<div class="table-responsive">
<table class="table table-hover" data-datatable data-export-name="batch-management-report" data-fixed-columns>
        <thead><tr><th>S/N</th><th>Batch Number</th><th>Batch Balance</th><th>Manufacture Date</th><th>Received Date</th><th>Expire Date</th></tr></thead>
        <tbody>
            @forelse($batches as $i => $b)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $b->batch_number }}</td>
                    <td>{{ $b->quantity_remaining }}</td>
                    <td>{{ $b->manufacture_date->toDateString() }}</td>
                    <td>{{ $b->received_date->toDateString() }}</td>
                    <td>{{ $b->expiry_date->toDateString() }}</td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>