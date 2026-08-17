@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Insurance Expiry</h2></div>

<form id="reportFilterForm" class="row g-3 mb-3" data-endpoint="{{ route('fleet.reports.insurance_expiry_data') }}">
    <div class="col-md-6">
        <label class="form-label">Expiring Within</label>
        <select name="within_days" class="form-select">
            <option value="30">30 days</option>
            <option value="60" selected>60 days</option>
            <option value="90">90 days</option>
            <option value="180">180 days</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Subdepartment</label>
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