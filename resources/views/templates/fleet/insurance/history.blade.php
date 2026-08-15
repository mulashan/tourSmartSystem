@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')
<div class="settings-panel-head"><h2>Insurance History — {{ $vehicle->registration_no }}</h2></div>

<div class="table-responsive">
    <table class="table table-hover" data-datatable data-export-name="insurance-history">
        <thead>
            <tr><th>Company</th><th>Policy No.</th><th>Type</th><th>Coverage</th><th>Start</th><th>Expiry</th><th>Premium</th><th>Recorded By</th></tr>
        </thead>
        <tbody>
            @forelse($insurances as $i)
                <tr>
                    <td>{{ $i->insurance_company }}</td>
                    <td>{{ $i->policy_number }}</td>
                    <td>{{ $i->insuranceType->name ?? '—' }}</td>
                    <td>{{ $i->coverages->pluck('name')->implode(', ') ?: '—' }}</td>
                    <td>{{ $i->start_date }}</td>
                    <td>{{ $i->expire_date }}</td>
                    <td>{{ $i->premium ? number_format($i->premium, 2) : '—' }}</td>
                    <td>{{ $i->createdBy->name ?? '—' }}</td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>
@endsection