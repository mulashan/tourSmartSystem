@extends('templates.app')

@section('content')
<div class="card">
    <div class="card-header">Select your working location</div>
    <div class="card-body">
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($subdepartments->isEmpty())
            <p class="text-muted">You have no Sub Departments assigned under {{ $moduleLabel }}. Contact an administrator.</p>
        @else
            <form method="POST" action="{{ route('storage-supplies.select-subdepartment.store', ['module' => $module]) }}" class="d-flex align-items-end gap-3">
                @csrf
                <div class="flex-grow-1" style="max-width: 400px;">
                    <label class="form-label fw-bold">Sub Department <span class="text-danger">*</span></label>
                    <select name="subdepartment_id" class="form-select" required>
                        <option value="">Select...</option>
                        @foreach($subdepartments as $sub)
                            <option value="{{ $sub->Subdepartment_ID }}">{{ $sub->Subdepartment_Name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-warning fw-bold">OPEN</button>
            </form>
        @endif
    </div>
</div>
@endsection