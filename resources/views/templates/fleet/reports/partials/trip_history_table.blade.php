<div class="table-responsive">
    <table class="table table-hover" data-datatable data-export-name="trip-history">
        <thead><tr><th>S/N</th><th>Trip No.</th><th>Vehicle</th><th>Driver</th><th>Destination</th><th>Start</th><th>End</th><th>Status</th></tr></thead>
        <tbody>
            @forelse($rows as $i => $t)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $t->trip_number }}</td>
                    <td>{{ $t->vehicle->registration_no ?? '—' }}</td>
                    <td>{{ $t->driver->Employee_Name ?? '—' }}</td>
                    <td>{{ $t->destination }}</td>
                    <td>{{ $t->start_date }}</td>
                    <td>{{ $t->end_date }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $t->status)) }}</td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>