@extends('templates.app')

@section('content')
<div class="card mx-auto mt-4" style="max-width: 500px;">
    <div class="card-header">Change Branch</div>
    <div class="card-body">
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <p class="text-muted">Currently working in: <strong>{{ session('active_branch_name') }}</strong></p>

        <form method="POST" action="{{ route('branch.change.submit') }}">
            @csrf
            <label class="form-label">Select Branch</label>
            <select name="branch_id" class="form-select mb-3" required>
                <option value="">Select...</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->Branch_ID }}" {{ $branch->Branch_ID == $currentBranchId ? 'selected' : '' }}>
                        {{ $branch->Branch_Name }}
                    </option>
                @endforeach
            </select>
            <div class="alert alert-warning small">
                <i class="bi bi-exclamation-triangle"></i> Switching branches will clear your currently selected Sub Department, if any.
            </div>
            <button type="submit" class="btn btn-primary">Switch Branch</button>
            <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection