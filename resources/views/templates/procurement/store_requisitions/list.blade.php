@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="settings-panel-head"><h2>Store Requisitions</h2></div>

<section class="section">
    <div class="card">
        <div class="card-body">
            <div class="row mt-3 mb-4">
                <div class="col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search store requisitions...">
                    </div>
                </div>
                <div class="col-lg-8 text-end">
                    <button class="btn btn-outline-success"><i class="bi bi-download"></i> Export</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle" style="min-width: 1100px;">
                    <thead class="table-light">
                        <tr>
                            <th>Order Number</th><th>Order Date</th><th>Store Ordering</th>
                            <th>Order Description</th><th>Created By</th><th>Supplier</th><th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td><strong>{{ $item->id }}</strong></td>
                                <td>{{ $item->order_date }}</td>
                                <td>{{ $item->subdepartment->Subdepartment_Name ?? '—' }}</td>
                                <td>{{ $item->order_description }}</td>
                                <td>{{ $item->preparedBy->name ?? '—' }}</td>
                                <td>
                                    <select class="form-select form-select-sm js-preview-supplier" style="min-width:160px;">
                                        <option value="">Select Supplier (optional)</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->supplier_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('procurement.store_requisitions.create_po', $item->id) }}" class="btn btn-sm btn-info text-white">
                                        Create Purchase Order
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-primary js-preview-btn" data-id="{{ $item->id }}">
                                        <i class="bi bi-eye"></i> Preview
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No approved store requisitions awaiting a Purchase Order.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <span class="text-muted">Showing {{ $items->count() }} records</span>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    window.procurementPreviewBase = '{{ url("procurement/store-requisitions") }}';

    $('.js-preview-btn').on('click', function () {
        const id = $(this).data('id');
        const supplierId = $(this).closest('tr').find('.js-preview-supplier').val();
        const url = window.procurementPreviewBase + '/' + id + '/preview' + (supplierId ? ('?supplier_id=' + supplierId) : '');
        window.open(url, '_blank');
    });
</script>
@endsection
