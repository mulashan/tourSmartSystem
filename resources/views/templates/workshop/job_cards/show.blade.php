@extends('templates.app')

@section('content')
@php
    $labourTotal = $jobCard->labourEntries->sum('amount');
    $partsTotal = $jobCard->partsUsed->sum('total');
    $firstVisibleWorkflowStep = collect($workflowAccess)
        ->filter(fn ($access) => $access['visible'] ?? false)
        ->keys()
        ->first() ?? 1;
@endphp

<div class="pagetitle d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>{{ $jobCard->job_no }}</h1>
        <p class="text-muted mb-0">{{ $jobCard->customer->name }} - {{ $jobCard->vehicle->registration_no }}</p>
    </div>

    <div class="d-flex gap-2">
        @include('templates.workshop.partials.status-badge', ['status' => $jobCard->status])
        <a href="{{ route('workshop.job-cards.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<section class="section">
    <div class="settings-shell workshop-job-shell">
        <aside class="settings-sidebar workshop-flow-sidebar">
            @foreach($workflowCards as $index => $step)
                @php
                    $stepNumber = $index + 1;
                    $stepAccess = $workflowAccess[$stepNumber] ?? ['unlocked' => true, 'message' => null];
                @endphp
                @continue(! ($stepAccess['visible'] ?? true))
                <a href="#workshop-step-{{ $stepNumber }}"
                   class="settings-nav-link {{ $stepNumber === $firstVisibleWorkflowStep ? 'active' : '' }} {{ $stepAccess['unlocked'] ? '' : 'disabled' }}"
                   data-workshop-tab="{{ $stepNumber }}"
                   data-workshop-locked="{{ $stepAccess['unlocked'] ? 'false' : 'true' }}"
                   @if(! $stepAccess['unlocked']) aria-disabled="true" tabindex="-1" title="{{ $stepAccess['message'] }}" @endif>
                    {{ $step->name }}
                </a>
            @endforeach
        </aside>

        <section class="settings-panel">
    <div class="row g-3 mb-4" id="workshop-step-1" data-workshop-panel="1">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Job Card Information</h5>
                    <div class="row g-3">
                        <div class="col-md-4"><span class="text-muted small">Date</span><div>{{ optional($jobCard->opened_date)->format('d M Y') }}</div></div>
                        <div class="col-md-4"><span class="text-muted small">Vehicle</span><div>{{ $jobCard->vehicle->registration_no }} {{ $jobCard->vehicle->make }} {{ $jobCard->vehicle->model }}</div></div>
                        <div class="col-md-4"><span class="text-muted small">Odometer</span><div>{{ $jobCard->odometer_reading ? number_format($jobCard->odometer_reading) : '-' }}</div></div>
                        <div class="col-md-4"><span class="text-muted small">Fuel Level</span><div>{{ $jobCard->fuel_level ?: '-' }}</div></div>
                        <div class="col-md-4"><span class="text-muted small">Priority</span><div>{{ ucfirst($jobCard->priority) }}</div></div>
                        <div class="col-md-4"><span class="text-muted small">Expected Completion</span><div>{{ optional($jobCard->expected_completion)->format('d M Y') ?: '-' }}</div></div>
                        <div class="col-12"><span class="text-muted small">Reported Problems</span><div>{{ $jobCard->reported_problems }}</div></div>
                        <div class="col-12"><span class="text-muted small">Remarks</span><div>{{ $jobCard->remarks ?: '-' }}</div></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Cost Summary</h5>
                    <div class="d-flex justify-content-between border-bottom py-2"><span>Labour</span><strong>{{ number_format($labourTotal, 2) }}</strong></div>
                    <div class="d-flex justify-content-between border-bottom py-2"><span>Parts</span><strong>{{ number_format($partsTotal, 2) }}</strong></div>
                    <div class="d-flex justify-content-between py-2"><span>Total Before Tax</span><strong>{{ number_format($labourTotal + $partsTotal, 2) }}</strong></div>
                    @if($jobCard->invoice)
                        <div class="alert alert-light border mt-3 mb-0">
                            <strong>{{ $jobCard->invoice->invoice_no }}</strong>
                            <div class="small text-muted">Grand Total: {{ number_format($jobCard->invoice->grand_total, 2) }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-12 {{ ! ($workflowAccess[2]['visible'] ?? true) ? 'd-none' : '' }}" id="workshop-step-2" data-workshop-panel="2">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                        <h5 class="card-title mb-0">Repair Orders</h5>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#repairOrderModal"><i class="bi bi-plus-circle"></i> Add</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" data-datatable data-export-name="{{ $jobCard->job_no }}-Repair-Orders" data-page-length="50">
                            <thead class="table-light"><tr><th>S/N</th><th>Type</th><th>Location</th><th>Vendor</th><th>Hours</th><th>External Cost</th><th>Status</th></tr></thead>
                            <tbody>
                                @foreach($jobCard->repairOrders as $i => $order)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td><strong>{{ $order->repair_type }}</strong><div class="text-muted small">{{ $order->description }}</div></td>
                                        <td>{{ ($order->maintenance_location ?? 'in_house') === 'outside' ? 'Outside Workshop' : 'In-house Workshop' }}</td>
                                        <td>{{ $order->vendor_name ?: '-' }}</td>
                                        <td>{{ $order->estimated_hours }}</td>
                                        <td>{{ ($order->maintenance_location ?? 'in_house') === 'outside' ? number_format($order->external_cost ?? $order->estimated_cost, 2) : '-' }}</td>
                                        <td>{{ ucfirst($order->status) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12 {{ ! ($workflowAccess[3]['visible'] ?? true) ? 'd-none' : '' }}" id="workshop-step-3" data-workshop-panel="3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                        <h5 class="card-title mb-0">Diagnosis</h5>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#diagnosisModal"><i class="bi bi-pencil-square"></i> Save</button>
                    </div>
                    @if($jobCard->diagnosis)
                        <p><span class="text-muted small">Findings</span><br>{{ $jobCard->diagnosis->findings }}</p>
                        <p><span class="text-muted small">Recommendation</span><br>{{ $jobCard->diagnosis->recommendation ?: '-' }}</p>
                        <div class="d-flex gap-3">
                            <span>Hours: <strong>{{ $jobCard->diagnosis->estimated_hours }}</strong></span>
                            <span>Parts: <strong>{{ number_format($jobCard->diagnosis->estimated_parts_cost, 2) }}</strong></span>
                            <span class="badge {{ $jobCard->diagnosis->approved ? 'bg-success' : 'bg-secondary' }}">{{ $jobCard->diagnosis->approved ? 'Approved' : 'Pending' }}</span>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">No diagnosis recorded.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-12 {{ ! ($workflowAccess[4]['visible'] ?? true) ? 'd-none' : '' }}" id="workshop-step-4" data-workshop-panel="4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                        <h5 class="card-title mb-0">Mechanics Assignment</h5>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#mechanicModal"><i class="bi bi-person-plus"></i> Assign</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" data-datatable data-export-name="{{ $jobCard->job_no }}-Mechanics-Assignment" data-page-length="50">
                            <thead class="table-light"><tr><th>S/N</th><th>Mechanic</th><th>Role</th><th>Hours</th><th>%</th><th>Status</th></tr></thead>
                            <tbody>
                                @foreach($jobCard->mechanicAssignments as $i => $assignment)
                                    <tr><td>{{ $i + 1 }}</td><td>{{ $assignment->mechanic->display_name }}</td><td>{{ $assignment->role ?: '-' }}</td><td>{{ $assignment->hours_worked }}</td><td>{{ $assignment->completion_percent }}%</td><td>{{ ucfirst($assignment->status) }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12 {{ ! ($workflowAccess[5]['visible'] ?? true) ? 'd-none' : '' }}" id="workshop-step-5" data-workshop-panel="5">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                        <h5 class="card-title mb-0">Labour Entries</h5>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#labourModal"><i class="bi bi-clock-history"></i> Add</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" data-datatable data-export-name="{{ $jobCard->job_no }}-Labour-Entries" data-page-length="50">
                            <thead class="table-light"><tr><th>S/N</th><th>Mechanic</th><th>Work</th><th>Hours</th><th>Amount</th></tr></thead>
                            <tbody>
                                @foreach($jobCard->labourEntries as $i => $entry)
                                    <tr><td>{{ $i + 1 }}</td><td>{{ $entry->mechanic->display_name }}</td><td>{{ $entry->work_done }}</td><td>{{ $entry->hours }}</td><td>{{ number_format($entry->amount, 2) }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12 {{ ! ($workflowAccess[6]['visible'] ?? true) ? 'd-none' : '' }}" id="workshop-step-6" data-workshop-panel="6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                        <h5 class="card-title mb-0">Parts Used</h5>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#partModal"><i class="bi bi-box-seam"></i> Issue</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" data-datatable data-export-name="{{ $jobCard->job_no }}-Parts-Used" data-page-length="50">
                            <thead class="table-light"><tr><th>S/N</th><th>Part</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
                            <tbody>
                                @foreach($jobCard->partsUsed as $i => $part)
                                    <tr><td>{{ $i + 1 }}</td><td>{{ $part->part->product_name }}</td><td>{{ $part->quantity }}</td><td>{{ number_format($part->unit_price, 2) }}</td><td>{{ number_format($part->total, 2) }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12 {{ ! ($workflowAccess[7]['visible'] ?? true) ? 'd-none' : '' }}" id="workshop-step-7" data-workshop-panel="7">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                        <h5 class="card-title mb-0">Complete Repair</h5>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#completionModal"><i class="bi bi-check2-circle"></i> Complete</button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <span class="text-muted small">Completion</span>
                            <div>{{ $jobCard->completion ? optional($jobCard->completion->completed_date)->format('d M Y') : 'Pending' }}</div>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted small">Vehicle Tested</span>
                            <div>{{ $jobCard->completion?->vehicle_tested ? 'Yes' : 'No' }}</div>
                        </div>
                        <div class="col-12">
                            <span class="text-muted small">Notes</span>
                            <div>{{ $jobCard->completion->completion_notes ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12 {{ ! ($workflowAccess[8]['visible'] ?? true) ? 'd-none' : '' }}" id="workshop-step-8" data-workshop-panel="8">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                        <h5 class="card-title mb-0">Quality Inspection</h5>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#inspectionModal"><i class="bi bi-clipboard-check"></i> Inspect</button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <span class="text-muted small">Inspection Date</span>
                            <div>{{ $jobCard->qualityCheck ? optional($jobCard->qualityCheck->inspection_date)->format('d M Y') : 'Pending' }}</div>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted small">Decision</span>
                            <div>{{ $jobCard->qualityCheck ? ucfirst(str_replace('_', ' ', $jobCard->qualityCheck->status)) : 'Pending' }}</div>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted small">Inspector</span>
                            <div>{{ $jobCard->qualityCheck->inspector_id ?? '-' }}</div>
                        </div>
                        <div class="col-12">
                            <span class="text-muted small">Remarks</span>
                            <div>{{ $jobCard->qualityCheck->remarks ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12 {{ ! ($workflowAccess[9]['visible'] ?? true) ? 'd-none' : '' }}" id="workshop-step-9" data-workshop-panel="9">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                        <h5 class="card-title mb-0">Generate Invoice</h5>
                        @if($jobCard->invoice)
                            <button class="btn btn-sm btn-success" disabled title="Invoice already generated">
                                <i class="bi bi-receipt"></i> Invoice Generated
                            </button>
                        @else
                            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#invoiceModal">
                                <i class="bi bi-receipt"></i> Generate
                            </button>
                        @endif
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3"><span class="text-muted small">Labour Total</span><div>{{ number_format($labourTotal, 2) }}</div></div>
                        <div class="col-md-3"><span class="text-muted small">Parts Total</span><div>{{ number_format($partsTotal, 2) }}</div></div>
                        <div class="col-md-3"><span class="text-muted small">Invoice</span><div>{{ $jobCard->invoice->invoice_no ?? 'Not generated' }}</div></div>
                        <div class="col-md-3"><span class="text-muted small">Grand Total</span><div>{{ $jobCard->invoice ? number_format($jobCard->invoice->grand_total, 2) : '-' }}</div></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12 {{ ! ($workflowAccess[10]['visible'] ?? true) ? 'd-none' : '' }}" id="workshop-step-10" data-workshop-panel="10">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                        <h5 class="card-title mb-0">Close Job Card</h5>
                        <form method="POST" action="{{ route('workshop.job-cards.close', $jobCard) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-dark"><i class="bi bi-lock"></i> Close</button>
                        </form>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4"><span class="text-muted small">Current Status</span><div>{{ ucfirst(str_replace('_', ' ', $jobCard->status)) }}</div></div>
                        <div class="col-md-4"><span class="text-muted small">Invoice Required</span><div>{{ $jobCard->invoice ? 'Ready to close' : 'Generate invoice first' }}</div></div>
                        <div class="col-md-4"><span class="text-muted small">Job No</span><div>{{ $jobCard->job_no }}</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
        </section>
    </div>
</section>

<div class="modal fade" id="repairOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
        <form method="POST" action="{{ route('workshop.job-cards.repair-orders.store', $jobCard) }}">@csrf
            <div class="modal-header"><h5 class="modal-title">Add Repair Order</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><div class="row g-3">
                <div class="col-md-6"><label class="form-label">Repair Type</label><input class="form-control" name="repair_type" required></div>
                <div class="col-md-3">
                    <label class="form-label">Maintenance Location</label>
                    <select class="form-select" name="maintenance_location" id="repairMaintenanceLocation" required>
                        <option value="in_house">In-house Workshop</option>
                        <option value="outside">Outside Workshop / Vendor</option>
                    </select>
                </div>
                <div class="col-md-3"><label class="form-label">Estimated Hours</label><input type="number" step="0.01" class="form-control" name="estimated_hours" value="0"></div>
                <div class="col-md-6 external-workshop-fields d-none"><label class="form-label">Vendor Name</label><input class="form-control" name="vendor_name" id="repairVendorName"></div>
                <div class="col-md-3 external-workshop-fields d-none"><label class="form-label">External Workshop Cost</label><input type="number" step="0.01" min="0" class="form-control" name="external_cost" id="repairExternalCost" value="0"></div>
                <div class="col-md-4"><label class="form-label">Status</label><select class="form-select" name="status"><option value="open">Open</option><option value="in_progress">In Progress</option><option value="completed">Completed</option><option value="cancelled">Cancelled</option></select></div>
                <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"></textarea></div>
            </div></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary"><i class="bi bi-check-circle"></i> Save</button></div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="diagnosisModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
        <form method="POST" action="{{ route('workshop.job-cards.diagnosis.store', $jobCard) }}">@csrf
            <div class="modal-header"><h5 class="modal-title">Vehicle Diagnosis</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><div class="row g-3">
                <div class="col-md-6"><label class="form-label">Mechanic</label><select class="form-select" name="mechanic_id"><option value="">Select Mechanic</option>@foreach($mechanics as $mechanic)<option value="{{ $mechanic->id }}" @selected($jobCard->diagnosis?->mechanic_id === $mechanic->id)>{{ $mechanic->display_name }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label">Estimated Labour</label><input type="number" step="0.01" class="form-control" name="estimated_hours" value="{{ $jobCard->diagnosis->estimated_hours ?? 0 }}"></div>
                <div class="col-md-3"><label class="form-label">Estimated Parts Cost</label><input type="number" step="0.01" class="form-control" name="estimated_parts_cost" value="{{ $jobCard->diagnosis->estimated_parts_cost ?? 0 }}"></div>
                <div class="col-12"><label class="form-label">Symptoms</label><textarea class="form-control" name="symptoms" rows="2">{{ $jobCard->diagnosis->symptoms ?? '' }}</textarea></div>
                <div class="col-12"><label class="form-label">Inspection Findings</label><textarea class="form-control" name="findings" rows="3" required>{{ $jobCard->diagnosis->findings ?? '' }}</textarea></div>
                <div class="col-md-6"><label class="form-label">Root Cause</label><textarea class="form-control" name="root_cause" rows="3">{{ $jobCard->diagnosis->root_cause ?? '' }}</textarea></div>
                <div class="col-md-6"><label class="form-label">Recommended Repairs</label><textarea class="form-control" name="recommendation" rows="3">{{ $jobCard->diagnosis->recommendation ?? '' }}</textarea></div>
                <div class="col-12"><div class="form-check"><input type="checkbox" class="form-check-input" name="approved" value="1" @checked($jobCard->diagnosis?->approved)><label class="form-check-label">Approved</label></div></div>
            </div></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary"><i class="bi bi-check-circle"></i> Save Diagnosis</button></div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="mechanicModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
        <form method="POST" action="{{ route('workshop.job-cards.mechanics.store', $jobCard) }}">@csrf
            <div class="modal-header"><h5 class="modal-title">Assign Mechanic</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><div class="row g-3">
                <div class="col-md-6"><label class="form-label">Existing Mechanic</label><select class="form-select" name="mechanic_id"><option value="">Create From Employee / Name</option>@foreach($mechanics as $mechanic)<option value="{{ $mechanic->id }}">{{ $mechanic->display_name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Employee</label><select class="form-select" name="employee_id"><option value="">Select Employee</option>@foreach($employees as $employee)<option value="{{ $employee->Employee_ID }}">{{ $employee->Employee_Name }}</option>@endforeach</select></div>
                <div class="col-md-4"><label class="form-label">Name</label><input class="form-control" name="name"></div>
                <div class="col-md-4"><label class="form-label">Specialization</label><input class="form-control" name="specialization"></div>
                <div class="col-md-4"><label class="form-label">Hourly Rate</label><input type="number" step="0.01" class="form-control" name="hourly_rate" value="0"></div>
                <div class="col-md-3"><label class="form-label">Assigned Date</label><input type="date" class="form-control" name="assigned_date" value="{{ now()->toDateString() }}" required></div>
                <div class="col-md-3"><label class="form-label">Role</label><input class="form-control" name="role"></div>
                <div class="col-md-2"><label class="form-label">Hours</label><input type="number" step="0.01" class="form-control" name="hours_worked" value="0"></div>
                <div class="col-md-2"><label class="form-label">Completion %</label><input type="number" class="form-control" name="completion_percent" value="0" min="0" max="100"></div>
                <div class="col-md-2"><label class="form-label">Status</label><select class="form-select" name="status"><option value="assigned">Assigned</option><option value="working">Working</option><option value="completed">Completed</option></select></div>
            </div></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary"><i class="bi bi-check-circle"></i> Assign</button></div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="labourModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
        <form method="POST" action="{{ route('workshop.job-cards.labour.store', $jobCard) }}">@csrf
            <div class="modal-header"><h5 class="modal-title">Record Labour</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><div class="row g-3">
                <div class="col-md-4"><label class="form-label">Mechanic</label><select class="form-select" name="mechanic_id" required><option value="">Select Mechanic</option>@foreach($mechanics as $mechanic)<option value="{{ $mechanic->id }}">{{ $mechanic->display_name }} - {{ number_format($mechanic->hourly_rate, 2) }}</option>@endforeach</select></div>
                <div class="col-md-2"><label class="form-label">Hours</label><input type="number" step="0.01" class="form-control" name="hours" required></div>
                <div class="col-md-2"><label class="form-label">Rate</label><input type="number" step="0.01" class="form-control" name="rate" required></div>
                <div class="col-md-4"><label class="form-label">Date</label><input type="date" class="form-control" name="date" value="{{ now()->toDateString() }}" required></div>
                <div class="col-12"><label class="form-label">Work Done</label><textarea class="form-control" name="work_done" rows="3" required></textarea></div>
            </div></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary"><i class="bi bi-check-circle"></i> Record Labour</button></div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="partModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
        <form method="POST" action="{{ route('workshop.job-cards.parts.store', $jobCard) }}">@csrf
            <div class="modal-header"><h5 class="modal-title">Issue Spare Part</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><div class="row g-3">
                <div class="col-md-5"><label class="form-label">Part</label><select class="form-select" name="part_id" required><option value="">Select Part</option>@foreach($parts as $part)<option value="{{ $part->id }}">{{ $part->product_name }} {{ $part->product_code ? '(' . $part->product_code . ')' : '' }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label">Store</label><select class="form-select" name="subdepartment_id" required><option value="">Select Store</option>@foreach($subdepartments as $subdepartment)<option value="{{ $subdepartment->Subdepartment_ID }}">{{ $subdepartment->Subdepartment_Name }}</option>@endforeach</select></div>
                <div class="col-md-2"><label class="form-label">Quantity</label><input type="number" class="form-control" name="quantity" value="1" min="1" required></div>
                <div class="col-md-2"><label class="form-label">Unit Price</label><input type="number" step="0.01" class="form-control" name="unit_price" value="0" required></div>
                <div class="col-md-4"><label class="form-label">Issue Date</label><input type="date" class="form-control" name="issue_date" value="{{ now()->toDateString() }}" required></div>
            </div></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary"><i class="bi bi-check-circle"></i> Issue Part</button></div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="completionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
        <form method="POST" action="{{ route('workshop.job-cards.complete', $jobCard) }}">@csrf
            <div class="modal-header"><h5 class="modal-title">Complete Repair</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><div class="row g-3">
                <div class="col-md-4"><label class="form-label">Completed Date</label><input type="date" class="form-control" name="completed_date" value="{{ now()->toDateString() }}" required></div>
                <div class="col-md-4 d-flex align-items-end"><div class="form-check"><input type="checkbox" class="form-check-input" name="vehicle_tested" value="1"><label class="form-check-label">Vehicle Tested</label></div></div>
                <div class="col-md-4 d-flex align-items-end"><div class="form-check"><input type="checkbox" class="form-check-input" name="ready_for_inspection" value="1" checked><label class="form-check-label">Ready for Inspection</label></div></div>
                <div class="col-12"><label class="form-label">Final Notes</label><textarea class="form-control" name="completion_notes" rows="3">{{ $jobCard->completion->completion_notes ?? '' }}</textarea></div>
            </div></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary"><i class="bi bi-check-circle"></i> Mark Complete</button></div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="inspectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
        <form method="POST" action="{{ route('workshop.job-cards.inspect', $jobCard) }}">@csrf
            <div class="modal-header"><h5 class="modal-title">Quality Inspection</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><div class="row g-3">
                <div class="col-md-6"><label class="form-label">Inspection Date</label><input type="date" class="form-control" name="inspection_date" value="{{ now()->toDateString() }}" required></div>
                <div class="col-md-6"><label class="form-label">Decision</label><select class="form-select" name="status" required><option value="approved">Approved</option><option value="returned_for_rework">Returned for Rework</option></select></div>
                @foreach(['repair_completed' => 'Repair completed', 'road_test' => 'Road test', 'no_oil_leaks' => 'No oil leaks', 'brakes_checked' => 'Brakes checked', 'lights_working' => 'Lights working', 'complaint_resolved' => 'Customer complaint resolved'] as $field => $label)
                    <div class="col-md-4"><div class="form-check"><input type="checkbox" class="form-check-input" name="{{ $field }}" value="1" @checked($jobCard->qualityCheck?->{$field})><label class="form-check-label">{{ $label }}</label></div></div>
                @endforeach
                <div class="col-12"><label class="form-label">Remarks</label><textarea class="form-control" name="remarks" rows="3">{{ $jobCard->qualityCheck->remarks ?? '' }}</textarea></div>
            </div></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary"><i class="bi bi-check-circle"></i> Save Inspection</button></div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="invoiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
        <form method="POST" action="{{ route('workshop.job-cards.invoice', $jobCard) }}">@csrf
            <div class="modal-header"><h5 class="modal-title">Generate Invoice</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><div class="row g-3">
                @if($jobCard->invoice)
                    <div class="col-12">
                        <div class="alert alert-info mb-0">Invoice already generated for this job card.</div>
                    </div>
                @endif
                <div class="col-md-4"><label class="form-label">Labour Total</label><input class="form-control" value="{{ number_format($labourTotal, 2) }}" readonly></div>
                <div class="col-md-4"><label class="form-label">Parts Total</label><input class="form-control" value="{{ number_format($partsTotal, 2) }}" readonly></div>
                <div class="col-md-4"><label class="form-label">Tax Rate %</label><input type="number" step="0.01" class="form-control" name="tax_rate" value="18"></div>
                <div class="col-md-4"><label class="form-label">Discount</label><input type="number" step="0.01" class="form-control" name="discount" value="{{ $jobCard->invoice->discount ?? 0 }}"></div>
                <div class="col-md-4"><label class="form-label">Other Charges</label><input type="number" step="0.01" class="form-control" name="other_charges" value="{{ $jobCard->invoice->other_charges ?? 0 }}"></div>
                <div class="col-md-4"><label class="form-label">Status</label><select class="form-select" name="status"><option value="draft">Draft</option><option value="issued">Issued</option><option value="paid">Paid</option></select></div>
            </div></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-success" @disabled($jobCard->invoice)><i class="bi bi-receipt"></i> Generate Invoice</button></div>
        </form>
    </div></div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tabs = Array.from(document.querySelectorAll('[data-workshop-tab]'));
        const panels = Array.from(document.querySelectorAll('[data-workshop-panel]'));
        const storageKey = 'workshop-job-card-active-tab-{{ $jobCard->id }}';
        const firstUnlockedStep = tabs.find((tab) => tab.dataset.workshopLocked !== 'true')?.dataset.workshopTab || '1';
        const isUnlocked = (step) => tabs.some((tab) => tab.dataset.workshopTab === String(step) && tab.dataset.workshopLocked !== 'true');

        const showPanel = (step) => {
            const selectedStep = isUnlocked(step) ? String(step) : firstUnlockedStep;

            tabs.forEach((tab) => {
                const isActive = tab.dataset.workshopTab === selectedStep;
                tab.classList.toggle('active', isActive);
                tab.setAttribute('aria-current', isActive ? 'page' : 'false');
            });

            panels.forEach((panel) => {
                panel.classList.toggle('d-none', panel.dataset.workshopPanel !== selectedStep);
            });

            if (window.jQuery && $.fn.dataTable) {
                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
            }

            localStorage.setItem(storageKey, selectedStep);
        };

        const stepFromHash = () => {
            const match = window.location.hash.match(/^#workshop-step-(\d+)$/);
            return match ? match[1] : null;
        };

        tabs.forEach((tab) => {
            tab.addEventListener('click', (event) => {
                event.preventDefault();
                if (tab.dataset.workshopLocked === 'true') {
                    return;
                }

                const step = tab.dataset.workshopTab;
                history.replaceState(null, '', `#workshop-step-${step}`);
                showPanel(step);
            });
        });

        const repairMaintenanceLocation = document.getElementById('repairMaintenanceLocation');
        const repairExternalFields = Array.from(document.querySelectorAll('.external-workshop-fields'));

        const toggleRepairExternalFields = () => {
            const isOutsideWorkshop = repairMaintenanceLocation?.value === 'outside';

            repairExternalFields.forEach((field) => {
                field.classList.toggle('d-none', !isOutsideWorkshop);
                field.querySelectorAll('input, select, textarea').forEach((input) => {
                    input.required = isOutsideWorkshop;

                    if (!isOutsideWorkshop) {
                        input.value = input.type === 'number' ? '0' : '';
                    }
                });
            });
        };

        repairMaintenanceLocation?.addEventListener('change', toggleRepairExternalFields);
        toggleRepairExternalFields();

        showPanel(stepFromHash() || localStorage.getItem(storageKey) || '1');
    });
</script>
@endsection
