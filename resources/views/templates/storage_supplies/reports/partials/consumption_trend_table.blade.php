<div class="table-responsive">
<table class="table table-hover" data-datatable data-export-name="consumption-trend-report" data-fixed-columns>
        <thead>
            <tr>
                <th>S/N</th><th>Item Name</th>
                @foreach($months as $m)<th>{{ \Carbon\Carbon::createFromFormat('Y-m', $m)->format('M Y') }}</th>@endforeach
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r['item']->product_name }}</td>
                    @foreach($months as $m)<td>{{ $r['per_month'][$m] ?? 0 }}</td>@endforeach
                    <td><strong>{{ $r['total'] }}</strong></td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>