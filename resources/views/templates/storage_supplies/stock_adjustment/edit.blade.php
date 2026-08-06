@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="card mb-3">
    <div class="card-header">Edit Adjustment #{{ $adjustment->id }}</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Adjustment Number</label>
                <input type="text" class="form-control" value="{{ $adjustment->id }}" disabled>
            </div>
            <div class="col-md-3">
                <label class="form-label">Adjustment Date *</label>
                <input type="date" id="adjustmentDate" class="form-control" value="{{ $adjustment->adjustment_date }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Adjustment Officer</label>
                <input type="text" class="form-control" value="{{ $adjustment->officer->name ?? '—' }}" disabled>
            </div>
            <div class="col-md-3">
                <label class="form-label">Adjustment Store</label>
                <input type="text" class="form-control" value="{{ $adjustment->subdepartment->Subdepartment_Name ?? '—' }}" disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label">Adjustment Description *</label>
                <textarea id="description" class="form-control" rows="1" required>{{ $adjustment->description }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Adjustment Reason</label>
                <input type="text" class="form-control" value="{{ $adjustment->reason === 'add_stock_balance' ? 'Add Stock Balance' : 'Expired / Dump / Broken' }}" disabled>
                <small class="text-muted">Reason cannot be changed after creation.</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="adjustment-picker">
            <label class="text-danger">*</label>
            <select class="form-select mb-2" id="adjPickerCategory">
                <option value="">Item Category</option>
                @foreach($itemCategories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <input type="text" class="form-control mb-2" id="adjPickerSearch" placeholder="Item Name">
            <table class="table table-sm table-hover">
                <thead><tr><th>Item Name</th><th>{{ $adjustment->reason === 'add_stock_balance' ? 'Balance' : 'Store Balance' }}</th></tr></thead>
                <tbody id="adjPickerResults">
                    <tr><td colspan="2" class="text-muted text-center">Select a category or search to see items</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-md-8">
        <div class="table-responsive">
            <table class="table table-hover {{ $adjustment->reason !== 'add_stock_balance' ? 'd-none' : '' }}" id="addItemsTable" style="min-width:1000px;">
                <thead><tr><th>S/N</th><th>Item Name</th><th>UoM</th><th>Quantity</th><th>Amount</th><th>Action</th><th>Remove</th></tr></thead>
                <tbody></tbody>
            </table>
            <table class="table table-hover {{ $adjustment->reason === 'add_stock_balance' ? 'd-none' : '' }}" id="deductItemsTable" style="min-width:700px;">
                <thead><tr><th>S/N</th><th>Item Name</th><th>UoM</th><th>Store Balance</th><th>Quantity</th><th>Remove</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="text-end mt-3">
    <a href="{{ route('storage_supplies.stock_adjustment.new') }}" class="btn btn-secondary">Cancel</a>
    <button type="button" class="btn btn-outline-primary" id="js-save-adjustment">Save</button>
    <button type="button" class="btn btn-info text-white" id="js-save-submit-adjustment">Save and Submit for Approval</button>
</div>

@include('templates.storage_supplies.grn_without_po.partials.batch_modal')
@endsection

@php
$existingAddItems = $adjustment->reason === 'add_stock_balance'
    ? $adjustment->items->map(function ($line) {
        return [
            'id' => $line->item_id,
            'name' => $line->item->product_name ?? 'Unknown item',
            'uom' => $line->item->unitOfMeasure->name ?? '—',
            'batches' => $line->batches->map(function ($b) {
                return [
                    'batch_number' => $b->batch_number, 'units' => $b->units, 'items_per_unit' => $b->items_per_unit,
                    'quantity' => $b->quantity, 'buying_price' => $b->buying_price,
                    'manufacture_date' => optional($b->manufacture_date)->toDateString(),
                    'expiry_date' => optional($b->expiry_date)->toDateString(),
                    'received_date' => optional($b->received_date)->toDateString(),
                ];
            })->values(),
        ];
    })->values()
    : collect();

$existingDeductItems = $adjustment->reason !== 'add_stock_balance'
    ? $adjustment->items->map(function ($line) use ($balances) {
        return [
            'id' => $line->item_id,
            'name' => $line->item->product_name ?? 'Unknown item',
            'uom' => $line->item->unitOfMeasure->name ?? '—',
            'balance' => $balances->get($line->item_id, $line->quantity),
            'quantity' => $line->quantity,
        ];
    })->values()
    : collect();
@endphp

@section('scripts')
<script>
    window.stockAdjustmentRoutes = {
        update: '{{ route("storage_supplies.stock_adjustment.update", $adjustment->id) }}',
        itemsPicker: '{{ route("storage_supplies.stock_adjustment.items_picker") }}',
        draftList: '{{ route("storage_supplies.stock_adjustment.new") }}',
    };
    window.stockAdjustmentReason = '{{ $adjustment->reason }}';
    window.stockAdjustmentExistingAddItems = @json($existingAddItems);
    window.stockAdjustmentExistingDeductItems = @json($existingDeductItems);
</script>
<script src="{{ asset('assets/js/stock-adjustment-edit.js') }}"></script>
@endsection