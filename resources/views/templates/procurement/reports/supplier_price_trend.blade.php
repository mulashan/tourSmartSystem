{{-- supplier_price_trend.blade.php --}}
@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Supplier Price Trend</h2></div>

<form id="reportFilterForm" class="row g-3 mb-3" data-endpoint="{{ route('procurement.reports.supplier_price_trend_data') }}">
    <div class="col-md-4">
        <label class="form-label">Item</label>
        <select name="item_id" id="priceTrendItemPicker" class="form-select">
            <option value="">Select an item...</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Flag Jumps Above</label>
        <select name="jump_threshold_pct" class="form-select">
            <option value="10">10%</option>
            <option value="15" selected>15%</option>
            <option value="25">25%</option>
            <option value="50">50%</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Store</label>
        <select name="subdepartment_id" class="form-select">
            @foreach($subdepartments as $s)
                <option value="{{ $s->Subdepartment_ID }}" {{ $s->Subdepartment_ID == session('active_subdepartment_id') ? 'selected' : '' }}>{{ $s->Subdepartment_Name }}</option>
            @endforeach
        </select>
    </div>
</form>

<div id="reportResults"><div class="text-muted p-4">Select an item to see its price history.</div></div>
@endsection

@section('scripts')
<script>window.priceTrendItemsPickerRoute = '{{ route("procurement.reports.supplier_price_trend_items_picker") }}';</script>
<script src="{{ asset('assets/js/report-auto-filter.js') }}"></script>
<script>
(function whenJQueryReady(fn) {
    if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); }
})(function () {
    $(function () {
        $.get(window.priceTrendItemsPickerRoute).done(items => {
            const $select = $('#priceTrendItemPicker');
            items.forEach(i => $select.append(`<option value="${i.id}">${i.product_name}</option>`));
        });
    });
});
</script>
@endsection