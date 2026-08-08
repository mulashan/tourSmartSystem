{{-- pending_po_aging.blade.php --}}
@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Pending Purchase Orders Aging</h2></div>

<form id="reportFilterForm" class="row g-3 mb-3" data-endpoint="{{ route('procurement.reports.pending_po_aging_data') }}">
    <div class="col-md-6">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="">All (Draft + Pending Approval)</option>
            <option value="draft">Draft only</option>
            <option value="pending_approval">Pending Approval only</option>
        </select>
    </div>
    <div class="col-md-6">
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