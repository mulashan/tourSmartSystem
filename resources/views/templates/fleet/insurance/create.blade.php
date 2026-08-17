@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<form action="{{ route('fleet.insurance.store', $vehicle->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="card mb-3">
        <div class="card-header">New Insurance Record — {{ $vehicle->registration_no }}</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Insurance Company *</label>
                    <select name="insurance_company" class="form-select" required>
                        <option value="">Select...</option>
                        @foreach($insuranceCompanies as $c)<option value="{{ $c->name }}">{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label">Policy Number *</label><input type="text" name="policy_number" class="form-control" required></div>
                <div class="col-md-4">
                    <label class="form-label">Insurance Type *</label>
                    <select name="insurance_type_id" class="form-select" required>
                        <option value="">Select...</option>
                        @foreach($insuranceTypes as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label">Start Date *</label><input type="date" name="start_date" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label">Expire Date *</label><input type="date" name="expire_date" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label">Premium</label><input type="number" step="0.01" name="premium" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Contact</label><input type="text" name="contact" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Certificate/Document</label><input type="file" name="certificate_document" class="form-control"></div>
                <div class="col-12">
                    <label class="form-label">Coverage</label><br>
                    @foreach($coverages as $c)
                        <div class="form-check form-check-inline">
                            <input type="checkbox" name="coverage_ids[]" value="{{ $c->id }}" class="form-check-input" id="cov{{ $c->id }}">
                            <label class="form-check-label" for="cov{{ $c->id }}">{{ $c->name }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="text-end">
        <a href="{{ route('fleet.insurance.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-info text-white">Save</button>
    </div>
</form>
@endsection