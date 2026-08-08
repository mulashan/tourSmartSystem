<div class="table-responsive">
    <table class="table table-hover">
        <thead><tr><th>S/N</th><th>Requisition No.</th><th>Item Name</th><th>UoM</th><th>Requested</th><th>Issued</th><th>Received</th><th>Shortfall %</th></tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr class="{{ $r['shortfall_pct'] > 0 ? 'table-warning' : '' }}">
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r['requisition_no'] }}</td>
                    <td>{{ $r['item']->product_name ?? '—' }}</td>
                    <td>{{ $r['item']->unitOfMeasure->name ?? '-' }}</td>
                    <td>{{ $r['requested'] }}</td>
                    <td>{{ $r['issued'] ?? 'Not yet issued' }}</td>
                    <td>{{ $r['received'] ?? 'Not yet received' }}</td>
                    <td>{{ $r['shortfall_pct'] }}%</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted">No requisitions in this period.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>