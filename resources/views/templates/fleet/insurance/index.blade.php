@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Vehicle Insurance</h2></div>

<div class="table-responsive">
    <table class="table table-hover" data-datatable data-export-name="vehicle-insurance">
        <thead>
            <tr><th>Vehicle</th><th>Company</th><th>Policy No.</th><th>Type</th><th>Expiry</th><th>Status</th><th class="text-end">Action</th></tr>
        </thead>
        <tbody>
            @forelse($vehicles as $v)
                <tr>
                    <td>{{ $v->registration_no }}</td>
                    <td>{{ $v->current_insurance->insurance_company ?? '—' }}</td>
                    <td>{{ $v->current_insurance->policy_number ?? '—' }}</td>
                    <td>{{ $v->current_insurance->insuranceType->name ?? '—' }}</td>
                    <td>{{ $v->current_insurance->expire_date ?? '—' }}</td>
                    <td>
                        @if(! $v->current_insurance)
                            <span class="text-muted">No record</span>
                        @elseif($v->insurance_alert === 'expired')
                            <span class="badge bg-danger">Expired</span>
                        @elseif($v->insurance_alert === 'expiring')
                            <span class="badge bg-warning text-dark">Expiring in {{ $v->insurance_days_left }} days</span>
                        @else
                            <span class="badge bg-success">Active</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('fleet.insurance.create', $v->id) }}" class="btn btn-sm btn-info text-white">Update / Add</a>
                        <a href="{{ route('fleet.insurance.history', $v->id) }}" class="btn btn-sm btn-outline-secondary">View History</a>
                    </td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>
@endsection