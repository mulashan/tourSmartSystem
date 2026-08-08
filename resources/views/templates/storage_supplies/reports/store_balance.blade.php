@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Store Balance Report</h2></div>

<form id="reportFilterForm" class="row g-3 mb-3" data-endpoint="{{ route('storage_supplies.reports.store_balance_data') }}">
    <div class="col-md-4"><label class="form-label">Item Name</label><input type="text" name="item_name" class="form-control"></div>
</form>

<div id="reportResults"><div class="text-muted p-4">Loading...</div></div>
@endsection

@section('scripts')
<script src="{{ asset('assets/js/report-auto-filter.js') }}"></script>
@endsection