@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Store Issuing Report</h2></div>

<form id="reportFilterForm" class="row g-3 mb-3" data-endpoint="{{ route('storage_supplies.reports.store_issuing_data') }}">
    <div class="col-md-4"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control" value="{{ now()->toDateString() }}"></div>
    <div class="col-md-4"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-control" value="{{ now()->toDateString() }}"></div>
    <div class="col-md-4">
        <label class="form-label">Store Received</label>
        <select name="store_received_id" class="form-select">
            <option value="">All</option>
            @foreach($subdepartments as $s)<option value="{{ $s->Subdepartment_ID }}">{{ $s->Subdepartment_Name }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Store Issue</label>
        <select name="store_issue_id" class="form-select">
            <option value="">All</option>
            @foreach($subdepartments as $s)<option value="{{ $s->Subdepartment_ID }}">{{ $s->Subdepartment_Name }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-4"><label class="form-label">Item Name</label><input type="text" name="item_name" class="form-control"></div>
</form>

<div id="reportResults"><div class="text-muted p-4">Loading...</div></div>
@endsection

@section('scripts')
<script src="{{ asset('assets/js/report-auto-filter.js') }}"></script>
@endsection