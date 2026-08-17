@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Predictive Maintenance Due</h2></div>

<form id="reportFilterForm" class="row g-3 mb-3" data-endpoint="{{ route('fleet.reports.maintenance_due_data') }}">
    <div class="col-md-4">
        <label class="form-label">Flag Due Within</label>
        <select name="due_soon_km" class="form-select">
            <option value="500">500 km</option>
            <option value="1000" selected>1,000 km</option>
            <option value="2000">2,000 km</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Subdepartment</label>
        <select name="subdepartment_id" class="form-select">
            @foreach($subdepartments as $s)<option value="{{ $s->Subdepartment_ID }}" {{ $s->Subdepartment_ID == session('active_subdepartment_id') ? 'selected' : '' }}>{{ $s->Subdepartment_Name }}</option>@endforeach
        </select>
    </div>
</form>

<div id="reportResults"><div class="text-muted p-4">Loading...</div></div>
@endsection

@section('scripts')
<script src="{{ asset('assets/js/report-auto-filter.js') }}"></script>
@endsection