@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Trip History</h2></div>

<form id="reportFilterForm" class="row g-3 mb-3" data-endpoint="{{ route('fleet.reports.trip_history_data') }}">
    <div class="col-md-3"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control" value="{{ now()->startOfMonth()->toDateString() }}"></div>
    <div class="col-md-3"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-control" value="{{ now()->toDateString() }}"></div>
    <div class="col-md-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="">All</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="assigned">Assigned</option>
            <option value="ready">Ready</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
            <option value="closed">Closed</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>
    <div class="col-md-3">
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