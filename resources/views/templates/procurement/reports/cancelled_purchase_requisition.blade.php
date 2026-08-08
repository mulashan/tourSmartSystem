{{-- cancelled_purchase_requisition.blade.php --}}
@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Cancelled Purchase Requisition</h2></div>

<form id="reportFilterForm" class="row g-3 mb-3" data-endpoint="{{ route('procurement.reports.cancelled_purchase_requisition_data') }}">
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