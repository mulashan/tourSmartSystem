@extends('templates.app')

@section('content')
<div class="pagetitle d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>Workshop Dashboard</h1>
        <p class="text-muted mb-0">Track workshop jobs, costs, inspections and invoices.</p>
    </div>

    <a href="{{ route('workshop.job-cards.index') }}" class="btn btn-primary">
        <i class="bi bi-card-checklist"></i> Job Cards
    </a>
</div>

<section class="section">
    <div class="row g-3 mb-4">
        @foreach([
            ['label' => 'New', 'value' => $counts['new'], 'icon' => 'bi-plus-circle', 'class' => 'text-primary'],
            ['label' => 'Open Jobs', 'value' => $counts['open'], 'icon' => 'bi-tools', 'class' => 'text-warning'],
            ['label' => 'Completed', 'value' => $counts['completed'], 'icon' => 'bi-check2-circle', 'class' => 'text-success'],
            ['label' => 'Invoiced', 'value' => $counts['invoiced'], 'icon' => 'bi-receipt', 'class' => 'text-dark'],
            ['label' => 'Closed', 'value' => $counts['closed'], 'icon' => 'bi-lock', 'class' => 'text-secondary'],
        ] as $metric)
            <div class="col-md">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <i class="bi {{ $metric['icon'] }} fs-3 {{ $metric['class'] }}"></i>
                        <div>
                            <div class="text-muted small">{{ $metric['label'] }}</div>
                            <strong class="fs-4">{{ $metric['value'] }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body py-3">
                    <div class="text-muted small">Labour Revenue</div>
                    <strong class="fs-4">{{ number_format($labourTotal, 2) }}</strong>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body py-3">
                    <div class="text-muted small">Parts Consumption</div>
                    <strong class="fs-4">{{ number_format($partsTotal, 2) }}</strong>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body py-3">
                    <div class="text-muted small">Invoice Total</div>
                    <strong class="fs-4">{{ number_format($invoiceTotal, 2) }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                <h5 class="card-title mb-0">Recent Job Cards</h5>
                <a href="{{ route('workshop.job-cards.index') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-arrow-right"></i> View All
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Job Card</th>
                            <th>Customer</th>
                            <th>Vehicle</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Opened</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentJobs as $job)
                            <tr>
                                <td><strong>{{ $job->job_no }}</strong></td>
                                <td>{{ $job->customer->name }}</td>
                                <td>{{ $job->vehicle->registration_no }}</td>
                                <td>{{ ucfirst($job->priority) }}</td>
                                <td>@include('templates.workshop.partials.status-badge', ['status' => $job->status])</td>
                                <td>{{ optional($job->opened_date)->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('workshop.job-cards.show', $job) }}" class="btn btn-sm btn-outline-primary" title="Open Job Card">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No workshop jobs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
