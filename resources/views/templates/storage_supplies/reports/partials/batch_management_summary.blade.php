<p class="text-muted">Select an Item Name above to see its batch breakdown. Showing all items with active stock for now:</p>
<div class="table-responsive">
    <table class="table table-hover">
        <thead><tr><th>S/N</th><th>Item Name</th><th>UoM</th><th>Batch Count</th><th>Total Balance</th></tr></thead>
        <tbody>
            @forelse($summary as $i => $s)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $s['item']->product_name }}</td>
                    <td>{{ $s['item']->unitOfMeasure->name ?? '-' }}</td>
                    <td>{{ $s['batch_count'] }}</td>
                    <td>{{ $s['total_balance'] }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">No batches with stock at this store.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>