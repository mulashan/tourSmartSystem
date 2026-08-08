{{-- partials/supplier_price_trend_table.blade.php --}}
@if(! $item)
    <div class="text-muted p-4">Select an item above to see its purchase price history.</div>
@else
    <div class="card mb-3">
        <div class="card-body">
            <strong>Item:</strong> {{ $item->product_name }} &nbsp;&nbsp; <strong>UoM:</strong> {{ $item->unitOfMeasure->name ?? '-' }}
            &nbsp;&nbsp; <strong>Flagging jumps ≥ {{ $jumpThresholdPct }}%</strong>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>S/N</th><th>Date</th><th>Supplier</th><th>LPO No.</th><th>Price</th><th>Change from Previous</th></tr></thead>
            <tbody>
                @forelse($rows as $i => $r)
                    <tr class="{{ $r['flagged'] ? 'table-danger' : '' }}">
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $r['date'] }}</td>
                        <td>{{ $r['supplier'] }}</td>
                        <td>{{ $r['lpo_no'] }}</td>
                        <td>{{ number_format($r['price'], 2) }}</td>
                        <td>
                            @if($r['change_pct'] === null)
                                —
                            @else
                                {{ $r['change_pct'] > 0 ? '+' : '' }}{{ $r['change_pct'] }}%
                                @if($r['flagged'])<span class="badge bg-danger ms-1">Flagged</span>@endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">No purchase history found for this item.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endif