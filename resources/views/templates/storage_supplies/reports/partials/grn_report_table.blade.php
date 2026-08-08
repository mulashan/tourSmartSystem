<div class="table-responsive">
    <table class="table table-hover">
        <thead><tr><th>S/N</th><th>Created Date</th><th>GRN No.</th><th>GRN Type</th><th>Supplier</th><th>Delivery Note No.</th><th>Delivery Date</th><th>Amount</th><th>Action</th></tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r['created_date'] }}</td>
                    <td>{{ $r['grn_no'] }}</td>
                    <td>{{ $r['type'] }}</td>
                    <td>{{ $r['supplier'] }}</td>
                    <td>{{ $r['delivery_note'] }}</td>
                    <td>{{ $r['delivery_date'] }}</td>
                    <td>{{ number_format($r['amount'], 2) }}</td>
                    <td><a href="{{ $r['preview_url'] }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a></td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted">No GRNs in this period.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>