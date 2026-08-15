@extends('templates.app')

@section('styles')
<style>
    .workflow-page {
        --workflow-blue: #0b49bd;
        --workflow-ink: #071842;
        --workflow-line: #bcd0f4;
    }

    .process-card {
        border: 1px solid var(--workflow-line);
        border-radius: 8px;
        background: #fff;
        overflow: hidden;
    }

    .process-table-area h6 {
        color: #0a39a4;
        font-weight: 800;
        font-size: .78rem;
        margin-bottom: .65rem;
        text-transform: uppercase;
    }

    .process-form .form-label {
        color: var(--workflow-ink);
        font-size: .76rem;
        font-weight: 700;
        margin-bottom: .25rem;
    }

    .process-form .form-control,
    .process-form .form-select {
        min-height: 38px;
        font-size: .82rem;
    }

    .process-table-area {
        padding: 1rem;
        background: #fbfdff;
    }

    .process-table {
        min-width: 920px;
    }

    .process-table th {
        color: #0a39a4;
        font-size: .72rem;
        white-space: nowrap;
    }

    .process-table td {
        font-size: .78rem;
    }

    .external-cost-fields.d-none {
        display: none !important;
    }

    .process-modal .modal-title {
        color: #0a39a4;
        font-weight: 800;
    }

    .workflow-tabs-shell {
        display: grid;
        grid-template-columns: 240px 1fr;
        gap: 1rem;
        align-items: start;
    }

    .workflow-tab-content {
        min-width: 0;
    }

    .workflow-vertical-tabs {
        border: 1px solid var(--workflow-line);
        border-radius: 8px;
        background: #fff;
        padding: .6rem;
        position: sticky;
        top: 96px;
        z-index: 2;
    }

    .workflow-tab-btn {
        width: 100%;
        display: flex;
        align-items: center;
        gap: .55rem;
        border: 0;
        border-radius: 6px;
        background: transparent;
        color: #6b7894;
        font-weight: 700;
        padding: .75rem .85rem;
        text-align: left;
    }

    .workflow-tab-btn.active {
        background: #eef2ff;
        color: #2847f5;
    }

    .workflow-tab-panel.d-none {
        display: none !important;
    }

    .vehicle-inspection-table-scroll {
        max-height: calc(100vh - 265px);
        overflow: auto;
        scrollbar-gutter: stable;
    }

    .vehicle-inspection-table-scroll .dataTables_wrapper,
    .vehicle-inspection-table-scroll table {
        min-width: 920px;
    }

    .vehicle-inspection-table-scroll thead th {
        position: sticky;
        top: 0;
        z-index: 1;
    }

    @media (max-width: 991.98px) {
        .workflow-tabs-shell {
            grid-template-columns: 1fr;
        }

        .workflow-vertical-tabs {
            position: static;
        }

        .vehicle-inspection-table-scroll {
            max-height: none;
        }
    }
</style>
@endsection

@section('content')
<div class="workflow-page">
    <div class="pagetitle d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>{{ $currentProcess['title'] ?? 'Workflow Management' }}</h1>
            <p class="text-muted mb-0">Manage {{ strtolower($currentProcess['title'] ?? 'workflow') }} form fields and records.</p>
        </div>
        @if(!empty($currentProcess['form_fields']) && ($currentProcess['slug'] ?? '') !== 'vehicle-inspection')
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#processFormModal">
                <i class="bi bi-plus-circle"></i> Add {{ $currentProcess['title'] }}
            </button>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <section class="section">
        @if(($currentProcess['slug'] ?? '') === 'vehicle-inspection')
            <div class="workflow-tabs-shell">
                <div class="workflow-vertical-tabs">
                    <button type="button" class="workflow-tab-btn active" data-vehicle-inspection-tab="pending">
                        <i class="bi bi-clipboard-plus"></i> Inspection
                    </button>
                    <button type="button" class="workflow-tab-btn" data-vehicle-inspection-tab="inspected">
                        <i class="bi bi-clipboard-check"></i> Already Inspected
                    </button>
                </div>

                <div class="workflow-tab-content">
                    <div class="workflow-tab-panel" data-vehicle-inspection-panel="pending">
                        <div class="process-card">
                            <div class="process-table-area">
                                <h6>Job Cards Needing Inspection</h6>
                                <div class="table-responsive vehicle-inspection-table-scroll">
                                    <table class="table table-bordered table-hover align-middle process-table mb-0" data-datatable data-export-name="Job Cards Needing Inspection" data-page-length="50" data-fixed-columns>
                                        <thead class="table-light">
                                            <tr>
                                                <th>S/N</th>
                                                <th>Job No</th>
                                                <th>Vehicle</th>
                                                <th>Customer / Driver</th>
                                                <th>Opened Date</th>
                                                <th>Priority</th>
                                                <th>Status</th>
                                                <th class="text-end no-export">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($pendingInspectionJobCards as $i => $jobCard)
                                                <tr>
                                                    <td>{{ $i + 1 }}</td>
                                                    <td><strong>{{ $jobCard->job_no }}</strong></td>
                                                    <td>{{ $jobCard->vehicle->registration_no }}<div class="text-muted small">{{ trim(($jobCard->vehicle->make ?? '') . ' ' . ($jobCard->vehicle->model ?? '')) }}</div></td>
                                                    <td>{{ $jobCard->customer->name }}</td>
                                                    <td>{{ optional($jobCard->opened_date)->format('d M Y') }}</td>
                                                    <td>{{ ucfirst($jobCard->priority) }}</td>
                                                    <td>@include('templates.workshop.partials.status-badge', ['status' => $jobCard->status])</td>
                                                    <td class="text-end no-export">
                                                        <button type="button"
                                                                class="btn btn-sm btn-primary js-open-inspection-form"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#processFormModal"
                                                                data-job-card-id="{{ $jobCard->id }}"
                                                                data-job-card-label="{{ $jobCard->job_no }} - {{ $jobCard->vehicle->registration_no }}">
                                                            <i class="bi bi-clipboard-plus"></i> Inspect
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center text-muted py-3">No job cards need inspection.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="workflow-tab-panel d-none" data-vehicle-inspection-panel="inspected">
                        <div class="process-card">
                            <div class="process-table-area">
                                <h6>Already Inspected</h6>
                                <div class="table-responsive vehicle-inspection-table-scroll">
                                    <table class="table table-bordered table-hover align-middle process-table mb-0" data-datatable data-export-name="Already Inspected Job Cards" data-page-length="50" data-fixed-columns>
                                        <thead class="table-light">
                                            <tr>
                                                <th>S/N</th>
                                                <th>Inspection No</th>
                                                <th>Job No</th>
                                                <th>Vehicle</th>
                                                <th>Customer / Driver</th>
                                                <th>Inspection Date</th>
                                                <th>Inspector</th>
                                                <th>Fuel Level</th>
                                                <th>Tyre Condition</th>
                                                <th>Battery Condition</th>
                                                <th>Fluid Status</th>
                                                <th>Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($inspectedVehicleInspections as $i => $inspection)
                                                <tr>
                                                    <td>{{ $i + 1 }}</td>
                                                    <td><strong>VI{{ str_pad((string) $inspection->id, 5, '0', STR_PAD_LEFT) }}</strong></td>
                                                    <td>{{ $inspection->jobCard->job_no ?? '-' }}</td>
                                                    <td>{{ $inspection->jobCard->vehicle->registration_no ?? '-' }}</td>
                                                    <td>{{ $inspection->jobCard->customer->name ?? '-' }}</td>
                                                    <td>{{ optional($inspection->inspection_date)->format('d M Y') }}</td>
                                                    <td>{{ $inspection->inspector_name ?: '-' }}</td>
                                                    <td>{{ $inspection->fuel_level ?: '-' }}</td>
                                                    <td>{{ $inspection->tyre_condition ?: '-' }}</td>
                                                    <td>{{ $inspection->battery_condition ?: '-' }}</td>
                                                    <td>{{ $inspection->fluid_status ?: '-' }}</td>
                                                    <td>{{ $inspection->remarks ?: '-' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="12" class="text-center text-muted py-3">No inspected job cards yet.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="row g-4">
                @foreach($processCards as $card)
                    <div class="col-12" id="{{ $card['slug'] }}">
                        <div class="process-card">
                            <div class="process-table-area">
                                <h6>{{ $card['title'] }} Records Table</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover align-middle process-table mb-0" data-datatable data-export-name="{{ $card['title'] }} Records" data-page-length="50" data-fixed-columns>
                                        <thead class="table-light">
                                            <tr>
                                                <th>S/N</th>
                                                @foreach($card['table_fields'] ?? $card['fields'] as $field)
                                                    <th>{{ $field }}</th>
                                                @endforeach
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($records as $i => $record)
                                                <tr>
                                                    <td>{{ $i + 1 }}</td>
                                                    @foreach($record as $value)
                                                        <td>{{ $value }}</td>
                                                    @endforeach
                                                    <td class="text-nowrap no-export">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" disabled title="Editing coming soon">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ count($card['table_fields'] ?? []) + 2 }}" class="text-center text-muted py-3">
                                                        No {{ strtolower($card['title']) }} records yet.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    @if(!empty($currentProcess['form_fields']))
        <div class="modal fade process-modal" id="processFormModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <form class="process-form" method="POST" action="{{ route('workshop.workflow-management.process.store', $currentProcess['slug']) }}" data-process-form="{{ $currentProcess['slug'] }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">{{ $currentProcess['title'] }} Form</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                @foreach($currentProcess['form_fields'] as $field)
                                    @php
                                        $fieldId = $currentProcess['slug'] . '-' . $field['name'];
                                        $isLongText = ($field['type'] ?? 'text') === 'textarea';
                                        $isExternalOnly = !empty($field['external_only']);
                                        $isCheckbox = ($field['type'] ?? 'text') === 'checkbox';
                                    @endphp

                                    <div class="{{ $isCheckbox ? 'col-lg-3 col-md-4 d-flex align-items-end' : ($isLongText ? 'col-lg-6' : 'col-lg-3 col-md-4') }} {{ $isExternalOnly ? 'external-cost-fields d-none' : '' }}">
                                        @if($isCheckbox)
                                            <div class="form-check">
                                                <input id="{{ $fieldId }}" type="checkbox" class="form-check-input" name="{{ $field['name'] }}" value="1">
                                                <label class="form-check-label" for="{{ $fieldId }}">{{ $field['label'] }}</label>
                                            </div>
                                        @else
                                            <label class="form-label" for="{{ $fieldId }}">{{ $field['label'] }}</label>
                                        @endif

                                        @if(($field['type'] ?? 'text') === 'vehicle')
                                            <select id="{{ $fieldId }}" class="form-select" name="{{ $field['name'] }}" @required(!empty($field['required']))>
                                                <option value="">Select Vehicle</option>
                                                @foreach($vehicles ?? [] as $vehicle)
                                                    <option value="{{ $vehicle->id }}">
                                                        {{ $vehicle->registration_no }} - {{ $vehicle->customer->name }}
                                                        {{ trim(($vehicle->make ?? '') . ' ' . ($vehicle->model ?? '')) ? ' - ' . trim(($vehicle->make ?? '') . ' ' . ($vehicle->model ?? '')) : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @elseif(($field['type'] ?? 'text') === 'job_card')
                                            <select id="{{ $fieldId }}" class="form-select" name="{{ $field['name'] }}" data-job-card-select @required(!empty($field['required']))>
                                                <option value="">Select Job Card</option>
                                                @foreach($jobCards ?? [] as $jobCard)
                                                    <option value="{{ $jobCard->id }}">{{ $jobCard->job_no }} - {{ $jobCard->vehicle->registration_no }}</option>
                                                @endforeach
                                            </select>
                                        @elseif(($field['type'] ?? 'text') === 'mechanic')
                                            <select id="{{ $fieldId }}" class="form-select" name="{{ $field['name'] }}" @required(!empty($field['required']))>
                                                <option value="">Select Technician</option>
                                                @foreach($mechanics ?? [] as $mechanic)
                                                    <option value="{{ $mechanic->id }}">{{ $mechanic->display_name }}</option>
                                                @endforeach
                                            </select>
                                        @elseif(($field['type'] ?? 'text') === 'part')
                                            <select id="{{ $fieldId }}" class="form-select" name="{{ $field['name'] }}" @required(!empty($field['required']))>
                                                <option value="">Select Part</option>
                                                @foreach($parts ?? [] as $part)
                                                    <option value="{{ $part->id }}">{{ $part->product_name }} {{ $part->product_code ? '(' . $part->product_code . ')' : '' }}</option>
                                                @endforeach
                                            </select>
                                        @elseif(($field['type'] ?? 'text') === 'subdepartment')
                                            <select id="{{ $fieldId }}" class="form-select" name="{{ $field['name'] }}" @required(!empty($field['required']))>
                                                <option value="">Select Store</option>
                                                @foreach($subdepartments ?? [] as $subdepartment)
                                                    <option value="{{ $subdepartment->Subdepartment_ID }}">{{ $subdepartment->Subdepartment_Name }}</option>
                                                @endforeach
                                            </select>
                                        @elseif(($field['type'] ?? 'text') === 'maintenance_location')
                                            <select id="{{ $fieldId }}" class="form-select" name="{{ $field['name'] }}" data-maintenance-location @required(!empty($field['required']))>
                                                <option value="in_house">In-house workshop</option>
                                                <option value="outside">Outside workshop / vendor</option>
                                            </select>
                                        @elseif(($field['type'] ?? 'text') === 'select')
                                            <select id="{{ $fieldId }}" class="form-select" name="{{ $field['name'] }}" @required(!empty($field['required']))>
                                                <option value="">Select</option>
                                                @foreach($field['options'] ?? [] as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        @elseif($isLongText)
                                            <textarea id="{{ $fieldId }}" class="form-control" name="{{ $field['name'] }}" rows="2" @required(!empty($field['required']))></textarea>
                                        @elseif(! $isCheckbox)
                                            <input id="{{ $fieldId }}"
                                                   type="{{ ($field['type'] ?? 'text') === 'date' ? 'date' : (($field['type'] ?? 'text') === 'number' ? 'number' : 'text') }}"
                                                   class="form-control"
                                                   name="{{ $field['name'] }}"
                                                   @if(($field['type'] ?? 'text') === 'number') step="0.01" min="0" @endif
                                                   @required(!empty($field['required']))>
                                        @else
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Save {{ $currentProcess['title'] }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-process-form="repair-maintenance"]').forEach(function (form) {
            const locationInputs = form.querySelectorAll('[data-maintenance-location]');
            const externalFields = form.querySelectorAll('.external-cost-fields');

            const toggleExternalCost = function () {
                const isOutside = Array.from(locationInputs).some(function (input) {
                    return input.value === 'outside';
                });

                externalFields.forEach(function (field) {
                    field.classList.toggle('d-none', !isOutside);
                    field.querySelectorAll('input, select, textarea').forEach(function (input) {
                        input.required = isOutside;
                        if (!isOutside) {
                            input.value = '';
                        }
                    });
                });
            };

            locationInputs.forEach(function (input) {
                input.addEventListener('change', toggleExternalCost);
            });

            toggleExternalCost();
        });

        document.querySelectorAll('[data-vehicle-inspection-tab]').forEach(function (tab) {
            tab.addEventListener('click', function () {
                const target = tab.dataset.vehicleInspectionTab;

                document.querySelectorAll('[data-vehicle-inspection-tab]').forEach(function (button) {
                    button.classList.toggle('active', button === tab);
                });

                document.querySelectorAll('[data-vehicle-inspection-panel]').forEach(function (panel) {
                    panel.classList.toggle('d-none', panel.dataset.vehicleInspectionPanel !== target);
                });

                if (window.jQuery && $.fn.dataTable) {
                    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
                }
            });
        });

        document.querySelectorAll('.js-open-inspection-form').forEach(function (button) {
            button.addEventListener('click', function () {
                const select = document.querySelector('#processFormModal [data-job-card-select]');

                if (!select) {
                    return;
                }

                select.value = button.dataset.jobCardId || '';
                select.dataset.lockedFromAction = 'true';
                select.classList.add('bg-light');
            });
        });
    });
</script>
@endsection
