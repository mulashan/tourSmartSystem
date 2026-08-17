@extends('templates.app')

@section('content')
<div class="pagetitle d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>Job Cards</h1>
        <p class="text-muted mb-0">Create and manage vehicle repair job cards.</p>
    </div>

    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addJobCardModal">
        <i class="bi bi-plus-circle"></i> New Job Card
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        {{ $errors->first() }}
    </div>
@endif

<section class="section">
    <div class="card">
        <div class="card-body">
            <div class="row mt-3 mb-4">
                <div class="col-lg-3 ms-auto">
                    <select class="form-select" onchange="if (this.value) window.location = this.value">
                        <option value="{{ route('workshop.job-cards.index') }}">All Statuses</option>
                        <option value="{{ route('workshop.job-cards.index', ['status' => 'open']) }}" @selected($statusFilter === 'open')>Open Jobs</option>
                        @foreach(['new', 'assigned', 'in_progress', 'waiting_parts', 'completed', 'invoiced', 'closed', 'cancelled'] as $status)
                            <option value="{{ route('workshop.job-cards.index', ['status' => $status]) }}" @selected($statusFilter === $status)>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle" data-datatable data-export-name="Workshop-Job-Cards" data-page-length="50" data-fixed-columns>
                    <thead class="table-light">
                        <tr>
                            <th>S/N</th>
                            <th>Job Card</th>
                            <th>Customer</th>
                            <th>Vehicle</th>
                            <th>Odometer</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Expected</th>
                            <th class="text-end no-export">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jobCards as $i => $job)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td><strong>{{ $job->job_no }}</strong></td>
                                <td>{{ $job->customer->name }}</td>
                                <td>
                                    {{ $job->vehicle->registration_no }}
                                    <div class="text-muted small">{{ trim(($job->vehicle->make ?? '') . ' ' . ($job->vehicle->model ?? '')) }}</div>
                                </td>
                                <td>{{ $job->odometer_reading ? number_format($job->odometer_reading) : '-' }}</td>
                                <td><span class="badge bg-light text-dark">{{ ucfirst($job->priority) }}</span></td>
                                <td>@include('templates.workshop.partials.status-badge', ['status' => $job->status])</td>
                                <td>{{ optional($job->expected_completion)->format('d M Y') ?: '-' }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('workshop.job-cards.show', $job) }}" class="btn btn-sm btn-outline-primary" title="Manage Job Card">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="addJobCardModal" tabindex="-1" aria-labelledby="addJobCardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('workshop.job-cards.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addJobCardModalLabel">Create Job Card</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Vehicle</label>
                            <select class="form-select" name="vehicle_id" required>
                                <option value="">Select Vehicle</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}">
                                        {{ $vehicle->registration_no }} - {{ $vehicle->customer->name }}
                                        {{ trim(($vehicle->make ?? '') . ' ' . ($vehicle->model ?? '')) ? ' - ' . trim(($vehicle->make ?? '') . ' ' . ($vehicle->model ?? '')) : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Opened Date</label>
                            <input type="date" class="form-control" name="opened_date" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Odometer Reading</label>
                            <input type="number" class="form-control" name="odometer_reading" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fuel Level</label>
                            <select class="form-select" name="fuel_level">
                                <option value="">Select Fuel Level</option>
                                <option>Empty</option>
                                <option>Quarter</option>
                                <option>Half</option>
                                <option>Three Quarters</option>
                                <option>Full</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Priority</label>
                            <select class="form-select" name="priority" required>
                                <option value="normal">Normal</option>
                                <option value="low">Low</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Expected Completion</label>
                            <input type="date" class="form-control" name="expected_completion">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Reported Problems</label>
                            <textarea class="form-control" name="reported_problems" rows="2" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Save Job Card
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
