@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Wastage / Loss Report</h2></div>

<div class="alert alert-info small">This report currently covers Stock Adjustment entries reasoned "Expired / Dump / Broken" only. GRN rejection tracking isn't built yet, so rejected-delivery losses aren't included here.</div>

<form id="reportFilterForm" class="row g-3 mb-3" data-endpoint="{{ route('storage_supplies.reports.wastage_loss_data') }}">
    <div class="col-md-4"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control" value="{{ now()->startOfMonth()->toDateString() }}"></div>
    <div class="col-md-4"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-control" value="{{ now()->toDateString() }}"></div>
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