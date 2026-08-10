<div class="table-responsive">
<table class="table table-hover" data-datatable data-export-name="Items-List" data-fixed-columns>
    <thead>
        <tr>
            <th>S/N</th>
            <th>Product Name</th>
            <th>Product Code</th>
            <th>Category</th>
            <th>Unit</th>
            <th>Status</th>
            <th>Reorder (Min/Max)</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->product_name }}</td>
                <td>{{ trim(($item->product_code_prefix ?? '') . ' ' . ($item->product_code ?? '')) ?: '—' }}</td>
                <td>{{ $item->itemCategory->name ?? '—' }}</td>
                <td>{{ $item->unitOfMeasure->name ?? '—' }}</td>
                <td><span class="badge bg-{{ $item->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($item->status) }}</span></td>
                <td>{{ $item->reorder_level ?? '—' }} / {{ $item->minimum_reorder_level ?? '—' }} / {{ $item->maximum_reorder_level ?? '—' }}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-secondary js-edit-item"
                        data-id="{{ $item->id }}"
                        data-product-name="{{ $item->product_name }}"
                        data-product-code-prefix="{{ $item->product_code_prefix }}"
                        data-product-code="{{ $item->product_code }}"
                        data-item-category-id="{{ $item->item_category_id }}"
                        data-unit-of-measure-id="{{ $item->unit_of_measure_id }}"
                        data-status="{{ $item->status }}"
                        data-reorder-level="{{ $item->reorder_level }}"
                        data-minimum-reorder-level="{{ $item->minimum_reorder_level }}"
                        data-maximum-reorder-level="{{ $item->maximum_reorder_level }}">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <!--button class="btn btn-sm btn-outline-danger js-delete-item" data-id="{{ $item->id }}">
                        <i class="bi bi-trash"></i>
                    </button-->
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted">No items yet.</td></tr>
        @endforelse
    </tbody>
</table>
</div>

