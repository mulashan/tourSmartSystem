@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="settings-panel-head"><h2>Previous Orders</h2></div>

<section class="section">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" data-datatable data-export-name="previous-orders" data-fixed-columns>
                    <thead class="table-light">
                        <tr>
                            <th>Order #</th><th>Date</th><th>Prepared By</th><th>Approved By</th>
                            <th>Approved At</th><th>Procurement Status</th><th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td><strong>{{ $item->id }}</strong></td>
                                <td>{{ $item->order_date }}</td>
                                <td>{{ $item->preparedBy->name ?? '—' }}</td>
                                <td>{{ $item->approvedBy->name ?? '—' }}</td>
                                <td>{{ $item->approved_at }}</td>
                                <td>
                                    <span style="color: {{ $item->procurement_status === 'rejected' ? '#c0392b' : ($item->localPurchaseOrder?->status === 'approved' ? '#27ae60' : '#888') }}">
                                        {{ $item->procurement_status_label }}
                                    </span>
                                    @if($item->procurement_status === 'rejected')
                                        <button type="button" class="btn btn-sm btn-link p-0 js-view-reason" data-reason="{{ $item->rejection_reason }}">View reason</button>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('storage_supplies.store_ordering.preview', $item->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        Preview
                                    </a>
                                </td>
                            </tr>
                        @empty
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
$('.js-view-reason').on('click', function () {
    Swal.fire({ icon: 'error', title: 'Rejection Reason', text: $(this).data('reason') });
});
</script>
@endsection
