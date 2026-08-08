@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Consumption Trend Report</h2></div>

<form id="reportFilterForm" class="row g-3 mb-3" data-endpoint="{{ route('storage_supplies.reports.consumption_trend_data') }}">
    <div class="col-md-4">
        <label class="form-label">Period</label>
        <select name="months_back" class="form-select">
            <option value="6" selected>Last 6 months</option>
            <option value="12">Last 12 months</option>
        </select>
    </div>
    <div class="col-md-4"><label class="form-label">Item Name</label><input type="text" name="item_name" class="form-control"></div>
    <div class="col-md-4">
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
<script src="{{ asset('assets/js/report-auto-filter.js') }}"></script>
@endsection