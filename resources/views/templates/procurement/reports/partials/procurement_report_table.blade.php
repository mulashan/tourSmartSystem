{{-- partials/procurement_report_table.blade.php --}}
<div class="table-responsive">
    <table class="table table-hover">
        <thead><tr><th>S/N</th><th>Stores</th><th>Purchasing Requisition Numbers</th><th>Amount</th></tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r['store'] }}</td>
                    <td>{{ $r['requisition_numbers'] }}</td>
                    <td>{{ number_format($r['amount'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted">No procurement activity in this period.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>