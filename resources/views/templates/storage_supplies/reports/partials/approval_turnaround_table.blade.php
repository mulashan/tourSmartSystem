<div class="card mb-3">
    <div class="card-header">Average Turnaround by Document Type</div>
    <div class="card-body">
        <div class="row">
            @forelse($averageByType as $type => $avgHours)
                <div class="col-md-4 mb-2"><strong>{{ $type }}:</strong> {{ $avgHours }} hrs</div>
            @empty
                <div class="text-muted">No approved documents in this period.</div>
            @endforelse
        </div>
    </div>
</div>

<div class="table-responsive">
<table class="table table-hover" data-datatable data-export-name="batch-management-report" data-fixed-columns>
        <thead><tr><th>S/N</th><th>Document Type</th><th>Doc No.</th><th>Submitted/Created</th><th>Approved At</th><th>Approver</th><th>Turnaround (hrs)</th></tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r['type'] }}</td>
                    <td>{{ $r['doc_no'] }}</td>
                    <td>{{ $r['started_at'] }}</td>
                    <td>{{ $r['approved_at'] }}</td>
                    <td>{{ $r['approver'] }}</td>
                    <td>{{ $r['hours'] ?? '—' }}</td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>