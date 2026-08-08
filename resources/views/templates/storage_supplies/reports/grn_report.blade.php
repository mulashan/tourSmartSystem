@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>GRN Report</h2></div>

<form id="reportFilterForm" class="row g-3 mb-3" data-endpoint="{{ route('storage_supplies.reports.grn_report_data') }}">
    <div class="col-md-4"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control" value="{{ now()->startOfMonth()->toDateString() }}"></div>
    <div class="col-md-4"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-control" value="{{ now()->toDateString() }}"></div>
    <div class="col-md-4">
        <label class="form-label">Supplier</label>
        <select name="supplier_id" class="form-select">
            <option value="">All</option>
            @foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->supplier_name }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-4"><label class="form-label">GRN No.</label><input type="text" name="grn_no" class="form-control"></div>
    <div class="col-md-4"><label class="form-label">LPO No.</label><input type="text" name="lpo_no" class="form-control"></div>
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