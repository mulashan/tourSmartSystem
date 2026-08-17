@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<style>
    /* NiceAdmin Custom Enhancements */
    .pagetitle h1 { font-size: 24px; font-weight: 700; color: #012970; }
    
    /* Stat Cards */
    .dashboard .info-card { padding-bottom: 10px; border-radius: 10px; border: none; box-shadow: 0px 0 30px rgba(1, 41, 112, 0.08); transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .dashboard .info-card:hover { transform: translateY(-3px); box-shadow: 0px 5px 30px rgba(1, 41, 112, 0.12); }
    .dashboard .card-icon { font-size: 26px; line-height: 0; width: 48px; height: 48px; flex-shrink: 0; flex-grow: 0; }
    
    /* Quick Action Alerts */
    .action-card { transition: all 0.2s ease-in-out; border-left: 4px solid transparent !important; }
    .action-card:hover { transform: translateX(4px); background-color: #f8f9fa !important; }
    .action-card.border-warning-left { border-left-color: #ffc107 !important; }
    .action-card.border-info-left { border-left-color: #0dcaf0 !important; }
    .action-card.border-danger-left { border-left-color: #dc3545 !important; }
    .action-card.border-primary-left { border-left-color: #0d6efd !important; }
    .action-card.border-success-left { border-left-color: #198754 !important; }

    /* Summary Metric Boxes */
    .metric-box { background: #f6f9ff; border-radius: 8px; padding: 12px 16px; margin-bottom: 8px; }
    
    /* Soft Badges */
    .bg-soft-primary { background-color: #e0bbf8; color: #0d6efd; }
    .bg-soft-success { background-color: #d1e7dd; color: #0f5132; }
    .bg-soft-warning { background-color: #fff3cd; color: #664d03; }
    .bg-soft-danger { background-color: #f8d7da; color: #842029; }
    .bg-soft-info { background-color: #cff4fc; color: #055160; }
    
    /* Table Styling */
    .table-custom th { font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #899bbd; background-color: #f6f9ff; border-bottom: none; }
    .table-custom td { vertical-align: middle; font-size: 14px; }
</style>

<div class="pagetitle mb-3">
    <h1>Fleet Control Dashboard</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    {{-- Row 1: Key Performance Indicator Cards (Single Row Layout) --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card info-card h-100">
                <div class="card-body p-3">
                    <h6 class="text-muted fs-7 fw-semibold mb-2">Total Vehicles</h6>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle bg-soft-primary d-flex align-items-center justify-content-center text-primary">
                            <i class="bi bi-truck"></i>
                        </div>
                        <div class="ps-2">
                            <h4 class="fw-bold mb-0 text-dark">{{ $vehicleCounts['total'] }}</h4>
                            <span class="text-muted small" style="font-size: 11px;">Fleet units</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-6">
            <div class="card info-card h-100">
                <div class="card-body p-3">
                    <h6 class="text-muted fs-7 fw-semibold mb-2">Available Now</h6>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle bg-soft-success d-flex align-items-center justify-content-center text-success">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="ps-2">
                            <h4 class="fw-bold mb-0 text-dark">{{ $vehicleCounts['available'] }}</h4>
                            <span class="text-muted small" style="font-size: 11px;">Ready to deploy</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-6">
            <div class="card info-card h-100">
                <div class="card-body p-3">
                    <h6 class="text-muted fs-7 fw-semibold mb-2">On Active Trip</h6>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle bg-soft-warning d-flex align-items-center justify-content-center text-warning">
                            <i class="bi bi-signpost-split"></i>
                        </div>
                        <div class="ps-2">
                            <h4 class="fw-bold mb-0 text-dark">{{ $vehicleCounts['on_trip'] }}</h4>
                            <span class="text-muted small" style="font-size: 11px;">In transit</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-6">
            <div class="card info-card h-100">
                <div class="card-body p-3">
                    <h6 class="text-muted fs-7 fw-semibold mb-2">In Maintenance</h6>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle bg-soft-danger d-flex align-items-center justify-content-center text-danger">
                            <i class="bi bi-tools"></i>
                        </div>
                        <div class="ps-2">
                            <h4 class="fw-bold mb-0 text-dark">{{ $vehicleCounts['maintenance'] }}</h4>
                            <span class="text-muted small" style="font-size: 11px;">At workshop</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 2: Action Pending Alerts --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body pt-3">
            <h5 class="card-title text-dark fw-bold mb-3 pb-0">
                <i class="bi bi-bell me-2 text-primary"></i>Pending Actions & Operations Queue
            </h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <a href="{{ route('fleet.itineraries.approve') }}" class="text-decoration-none">
                        <div class="action-card border-warning-left p-3 rounded bg-white shadow-sm d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-hourglass-split text-warning fs-5 me-3"></i>
                                <span class="fw-semibold text-dark">Itineraries Awaiting Approval</span>
                            </div>
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">{{ $pendingApproval }}</span>
                        </div>
                    </a>

                    <a href="{{ route('fleet.itineraries.assign') }}" class="text-decoration-none">
                        <div class="action-card border-info-left p-3 rounded bg-white shadow-sm d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-person-check text-info fs-5 me-3"></i>
                                <span class="fw-semibold text-dark">Awaiting Vehicle & Driver Assignment</span>
                            </div>
                            <span class="badge bg-info text-dark px-3 py-2 rounded-pill">{{ $awaitingAssignment }}</span>
                        </div>
                    </a>

                    <a href="{{ route('fleet.fuel.assign') }}" class="text-decoration-none">
                        <div class="action-card border-danger-left p-3 rounded bg-white shadow-sm d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-fuel-pump text-danger fs-5 me-3"></i>
                                <span class="fw-semibold text-dark">Trips/Legs Awaiting Fuel Assignment</span>
                            </div>
                            <span class="badge bg-danger px-3 py-2 rounded-pill">{{ $mainFuelWaiting + $legFuelWaiting }}</span>
                        </div>
                    </a>
                </div>

                <div class="col-md-6">
                    <a href="{{ route('fleet.fuel.issue') }}" class="text-decoration-none">
                        <div class="action-card border-primary-left p-3 rounded bg-white shadow-sm d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-droplet-half text-primary fs-5 me-3"></i>
                                <span class="fw-semibold text-dark">Fuel Assigned — Awaiting Issue</span>
                            </div>
                            <span class="badge bg-primary px-3 py-2 rounded-pill">{{ $fuelAssignedNotIssued }}</span>
                        </div>
                    </a>

                    <a href="{{ route('fleet.gate_pass.generate_list') }}" class="text-decoration-none">
                        <div class="action-card border-success-left p-3 rounded bg-white shadow-sm d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-file-earmark-check text-success fs-5 me-3"></i>
                                <span class="fw-semibold text-dark">Ready for Gate Pass</span>
                            </div>
                            <span class="badge bg-success px-3 py-2 rounded-pill">{{ $awaitingGatePass }}</span>
                        </div>
                    </a>

                    <a href="{{ route('fleet.insurance.index') }}" class="text-decoration-none">
                        <div class="action-card border-danger-left p-3 rounded bg-white shadow-sm d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-shield-exclamation text-danger fs-5 me-3"></i>
                                <span class="fw-semibold text-dark">Insurance Expiring / Expired (30 days)</span>
                            </div>
                            <span class="badge bg-danger px-3 py-2 rounded-pill">{{ $expiringInsurance->count() }}</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 3: Operational Summaries --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body pt-3">
                    <h5 class="card-title text-dark fw-bold pb-2"><i class="bi bi-fuel-pump-fill text-primary me-2"></i>Fuel Consumption</h5>
                    <div class="metric-box d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Total Quantity</span>
                        <span class="fw-bold text-dark">{{ number_format($fuelThisMonth['quantity'], 2) }} <small class="text-muted fs-7">L</small></span>
                    </div>
                    <div class="metric-box d-flex justify-content-between align-items-center mb-0">
                        <span class="text-muted small">Total Spend</span>
                        <span class="fw-bold text-primary">{{ number_format($fuelThisMonth['cost'], 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body pt-3">
                    <h5 class="card-title text-dark fw-bold pb-2"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Incidents & Maintenance</h5>
                    <div class="metric-box d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Incident Costs (This Month)</span>
                        <span class="fw-bold text-danger">{{ number_format($incidentCostThisMonth, 2) }}</span>
                    </div>
                    <div class="metric-box d-flex justify-content-between align-items-center mb-0">
                        <span class="text-muted small">Open Repair Orders</span>
                        <span class="fw-bold text-dark">{{ $openMaintenance->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body pt-3">
                    <h5 class="card-title text-dark fw-bold pb-2"><i class="bi bi-speedometer2 text-info me-2"></i>Fleet Status Overview</h5>
                    <div class="metric-box d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Inactive Vehicles</span>
                        <span class="fw-bold text-secondary">{{ $vehicleCounts['inactive'] }}</span>
                    </div>
                    <div class="metric-box d-flex justify-content-between align-items-center mb-0">
                        <span class="text-muted small">Active Trips Right Now</span>
                        <span class="fw-bold text-success">{{ $activeTrips->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 4: Detailed Tables --}}
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body pt-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h5 class="card-title text-dark fw-bold pb-0">Insurance Expiring Soon</h5>
                        <a href="{{ route('fleet.insurance.index') }}" class="btn btn-sm btn-light-primary text-primary fw-semibold">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>Vehicle</th>
                                    <th>Insurance Provider</th>
                                    <th>Status / Days Left</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expiringInsurance as $i)
                                    @php $daysLeft = (int) round(now()->diffInDays($i->expire_date, false)); @endphp
                                    <tr>
                                        <td class="fw-bold text-dark">{{ $i->vehicle->registration_no ?? '—' }}</td>
                                        <td>{{ $i->insurance_company }}</td>
                                        <td>
                                            @if($daysLeft < 0)
                                                <span class="badge bg-soft-danger text-danger"><i class="bi bi-x-circle me-1"></i>Expired</span>
                                            @else
                                                <span class="badge bg-soft-warning text-warning"><i class="bi bi-clock me-1"></i>{{ $daysLeft }} days left</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">
                                            <i class="bi bi-shield-check text-success fs-3 d-block mb-1"></i>
                                            All vehicle insurance policies are up to date.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body pt-3">
                    <h5 class="card-title text-dark fw-bold pb-2">Open Maintenance Orders</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>Vehicle</th>
                                    <th>Workshop</th>
                                    <th>Issue Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($openMaintenance as $m)
                                    <tr>
                                        <td class="fw-bold text-dark">{{ $m->vehicle->registration_no ?? '—' }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $m->workshop->Subdepartment_Name ?? '—' }}</span></td>
                                        <td class="text-muted">{{ \Illuminate\Support\Str::limit($m->problem, 35) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-3 text-muted">No pending workshop maintenance orders.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body pt-3">
                    <h5 class="card-title text-dark fw-bold pb-2">Recent Incidents & Fines</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Vehicle</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentIncidents as $r)
                                    <tr>
                                        <td>
                                            @if($r->type === 'accident')
                                                <span class="badge bg-soft-danger text-danger"><i class="bi bi-exclamation-octagon me-1"></i>Accident</span>
                                            @else
                                                <span class="badge bg-soft-info text-info"><i class="bi bi-receipt me-1"></i>Road Fine</span>
                                            @endif
                                        </td>
                                        <td class="fw-bold text-dark">{{ $r->vehicle->registration_no ?? '—' }}</td>
                                        <td class="text-muted small">{{ $r->incident_date }}</td>
                                        <td>
                                            <span class="badge {{ $r->status === 'open' ? 'bg-soft-warning text-dark' : 'bg-soft-success text-success' }}">
                                                {{ ucfirst($r->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-3 text-muted">No recent accidents or traffic fines reported.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection