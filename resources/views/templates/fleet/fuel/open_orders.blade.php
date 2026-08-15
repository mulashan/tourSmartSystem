@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head">
    <h2>Emergency / Open Fuel Order</h2>
    <button class="btn btn-info text-white" id="js-open-order-btn">Open New Order</button>
</div>

<table class="table table-hover" data-datatable data-export-name="fuel-open-orders">
    <thead><tr><th>Order #</th><th>Fuel Source</th><th>Opened By</th><th>Status</th><th>Items</th><th>Total Qty</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($orders as $o)
            <tr>
                <td><a href="{{ route('fleet.fuel.open_order_show', $o->id) }}">{{ $o->id }}</a></td><td>{{ $o->fuelSource->name ?? '—' }}</td><td>{{ $o->openedBy->name ?? '—' }}</td>
                <td><span class="badge bg-{{ $o->status === 'open' ? 'warning text-dark' : 'secondary' }}">{{ ucfirst($o->status) }}</span></td>
                <td>{{ $o->items->count() }}</td><td>{{ $o->items->sum('quantity') }}</td>
                <td class="text-end">
                    @if($o->status === 'open')
                        <button class="btn btn-sm btn-dark js-add-item" data-id="{{ $o->id }}">Add Vehicle Fuel</button>
                        <button class="btn btn-sm btn-outline-danger js-close-order" data-id="{{ $o->id }}">Close Order</button>
                    @endif
                </td>
            </tr>
        @empty
        @endforelse
    </tbody>
</table>

<div class="modal fade" id="openOrderModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form id="openOrderForm">
        <div class="modal-header"><h5 class="modal-title">Open New Fuel Order</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <label class="form-label">Fuel Source *</label>
            <select id="openOrderFuelSource" class="form-select" required><option value="">Select...</option>@foreach($fuelSources as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select>
            <div class="text-danger small mt-2" id="openOrderFormError"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Open Order</button></div>
    </form>
</div></div></div>

<div class="modal fade" id="addItemModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form id="addItemForm">
        <div class="modal-header"><h5 class="modal-title">Add Vehicle Fuel to Order</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" id="addItemOrderId">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Vehicle *</label><select id="itemVehicle" class="form-select" required><option value="">Select...</option>@foreach($vehicles as $v)<option value="{{ $v->id }}">{{ $v->registration_no }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Fuel Type *</label><select id="itemFuelType" class="form-select" required><option value="Petrol">Petrol</option><option value="Diesel">Diesel</option></select></div>
                <div class="col-md-4"><label class="form-label">Quantity *</label><input type="number" step="0.01" id="itemQty" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label">Unit Price *</label><input type="number" step="0.01" id="itemPrice" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label">Odometer</label><input type="number" id="itemOdometer" class="form-control"></div>
            </div>
            <div class="text-danger small mt-2" id="addItemFormError"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add</button></div>
    </form>
</div></div></div>
@endsection

@section('scripts')
<script>
(function whenJQueryReady(fn) { if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); } })(function () {
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
        const openOrderModal = new bootstrap.Modal(document.getElementById('openOrderModal'));
        const addItemModal = new bootstrap.Modal(document.getElementById('addItemModal'));

        $('#js-open-order-btn').on('click', () => { $('#openOrderForm')[0].reset(); $('#openOrderFormError').text(''); openOrderModal.show(); });
        $('#openOrderForm').on('submit', function (e) {
            e.preventDefault();
            $.post('{{ route("fleet.fuel.open_order_store") }}', { fuel_source_id: $('#openOrderFuelSource').val() })
                .done(() => Swal.fire({ icon: 'success', title: 'Order opened', timer: 1200, showConfirmButton: false }).then(() => location.reload()))
                .fail(xhr => $('#openOrderFormError').text(xhr.responseJSON?.message || 'Something went wrong.'));
        });

        $('.js-add-item').on('click', function () { $('#addItemOrderId').val($(this).data('id')); $('#addItemForm')[0].reset(); $('#addItemFormError').text(''); addItemModal.show(); });
        $('#addItemForm').on('submit', function (e) {
            e.preventDefault();
            $.post(`/fleet/fuel/open-orders/${$('#addItemOrderId').val()}/items`, {
                vehicle_id: $('#itemVehicle').val(), fuel_type: $('#itemFuelType').val(), quantity: $('#itemQty').val(), unit_price: $('#itemPrice').val(), odometer_reading: $('#itemOdometer').val(),
            })
                .done(() => Swal.fire({ icon: 'success', title: 'Added', timer: 1000, showConfirmButton: false }).then(() => location.reload()))
                .fail(xhr => $('#addItemFormError').text(xhr.responseJSON?.message || 'Something went wrong.'));
        });

        $('.js-close-order').on('click', function () {
            const id = $(this).data('id');
            Swal.fire({ icon: 'warning', title: 'Close this order?', showCancelButton: true, confirmButtonText: 'Yes, close it' }).then(result => {
                if (! result.isConfirmed) return;
                $.post(`/fleet/fuel/open-orders/${id}/close`)
                    .done(() => Swal.fire({ icon: 'success', title: 'Closed', timer: 1000, showConfirmButton: false }).then(() => location.reload()))
                    .fail(xhr => Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message }));
            });
        });
    });
});
</script>
@endsection