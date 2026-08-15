<div class="table-responsive">
    <table class="table table-hover" data-datatable data-export-name="maintenance-history">
        <thead><tr><th>S/N</th><th>Vehicle</th><th>Problem</th><th>Workshop</th><th>Status</th><th>Created</th></tr></thead>
        <tbody>
            @forelse($rows as $i => $o)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $o->vehicle->registration_no ?? '—' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($o->problem, 60) }}</td>
                    <td>{{ $o->workshop->Subdepartment_Name ?? '—' }}</td>
                    <td>{{ ucfirst($o->status) }}</td>
                    <td>{{ $o->created_at }}</td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>