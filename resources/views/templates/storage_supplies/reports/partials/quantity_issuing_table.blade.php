<div class="table-responsive">
<table class="table table-hover" data-datatable data-export-name="batch-management-report" data-fixed-columns>
        <thead>
            <tr>
                <th>S/N</th><th>Item Name</th><th>Folio No.</th>
                @foreach($stores as $store)<th>{{ $store->Subdepartment_Name }}</th>@endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r['item']->product_name }}</td>
                    <td>{{ trim(($r['item']->product_code_prefix ?? '') . ' ' . ($r['item']->product_code ?? '')) ?: '—' }}</td>
                    @foreach($stores as $store)<td>{{ $r['per_store'][$store->Subdepartment_ID] ?? 0 }}</td>@endforeach
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>