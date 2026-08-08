@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Stock Ledger</h2></div>

<form id="reportFilterForm" class="row g-3 mb-3" data-endpoint="{{ route('storage_supplies.reports.stock_ledger_data') }}">
    <div class="col-md-3"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control" value="{{ now()->toDateString() }}"></div>
    <div class="col-md-3"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-control" value="{{ now()->toDateString() }}"></div>
    <div class="col-md-3">
        <label class="form-label">Item</label>
        <select name="item_id" id="ledgerItemPicker" class="form-select">
            <option value="">All items</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Store</label>
        <select name="subdepartment_id" class="form-select">
            @foreach($subdepartments as $s)
                <option value="{{ $s->Subdepartment_ID }}" {{ $s->Subdepartment_ID == session('active_subdepartment_id') ? 'selected' : '' }}>{{ $s->Subdepartment_Name }}</option>
            @endforeach
        </select>
    </div>
</form>

<div id="reportResults"><div class="text-muted p-4">Loading...</div></div>
@endsection

@section('scripts')
<script>
    window.stockLedgerItemsPickerRoute = '{{ route("storage_supplies.reports.stock_ledger_items_picker") }}';
</script>
<script src="{{ asset('assets/js/report-auto-filter.js') }}"></script>
<script>
(function whenJQueryReady(fn) {
    if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); }
})(function () {
    $(function () {
        $.get(window.stockLedgerItemsPickerRoute).done(items => {
            const $select = $('#ledgerItemPicker');
            items.forEach(i => $select.append(`<option value="${i.id}">${i.product_name}</option>`));
        });
    });
});
</script>
@endsection