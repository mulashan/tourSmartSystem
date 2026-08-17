<div class="table-responsive">
    <table class="table table-hover" data-datatable data-export-name="destination-frequency">
        <thead><tr><th>Rank</th><th>Destination</th><th>Trip Count</th></tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr><td>{{ $i + 1 }}</td><td>{{ $r['destination'] }}</td><td>{{ $r['count'] }}</td></tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>