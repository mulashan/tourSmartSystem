@extends('templates.app')

@php
    $designations = collect($designations ?? []);
@endphp

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <section class="page-hero">
        <div><h1>Designations</h1><p>Manage employee designations and session time limits used in HR setup.</p></div>
        <div class="hero-actions"><button class="outline-btn"><i class="bi bi-download"></i> Export</button><button type="button" class="nice-action" data-bs-toggle="modal" data-bs-target="#addDesignationModal"><i class="bi bi-plus"></i> Add Designation</button></div>
    </section>

    <div class="content-split">
        <section class="panel table-panel">
            <div class="tabs-toolbar">
                <div class="soft-tabs"><button class="active">All <span>{{ $designations->count() }}</span></button><button>Active <span>{{ $designations->count() }}</span></button><button>Default 30 Min <span>{{ $designations->where('session_time_limit', 30)->count() }}</span></button><button>Custom <span>{{ $designations->where('session_time_limit', '!=', 30)->count() }}</span></button></div>
                <div class="toolbar-search"><i class="bi bi-search"></i><input placeholder="Search designation..."><button><i class="bi bi-sliders"></i> Time</button></div>
            </div>
            <table class="nice-table directory-table">
                <thead><tr><th><input type="checkbox"></th><th>Designation</th><th>Session Time Limit</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($designations as $designation)
                        <tr>
                            <td><input type="checkbox"></td>
                            <td><span class="tiny-avatar {{ $loop->iteration % 2 ? 'teal' : 'indigo' }}">{{ substr($designation->designation, 0, 1) }}</span><strong>{{ $designation->designation }}</strong><small>Designation #{{ $designation->designation_ID }}</small></td>
                            <td>{{ $designation->session_time_limit }} minutes</td>
                            <td><span class="state-dot active"></span>Active</td>
                            <td class="row-actions"><i class="bi bi-eye"></i><i class="bi bi-pencil"></i><i class="bi bi-three-dots"></i></td>
                        </tr>
                    @endforeach
                    @if($designations->isEmpty())
                        <tr><td colspan="5">No designations added yet.</td></tr>
                    @endif
                </tbody>
            </table>
        </section>

        <aside class="side-stack">
            <section class="panel"><h2>Designation Snapshot</h2><div class="snapshot-grid"><div><span>Total</span><strong>{{ $designations->count() }}</strong><small>Saved records</small></div><div class="green-bg"><span>Active</span><strong>{{ $designations->count() }}</strong><small>Ready to use</small></div><div class="amber-bg"><span>Default Time</span><strong>{{ $designations->where('session_time_limit', 30)->count() }}</strong><small>30 minutes</small></div><div class="red-bg"><span>Custom Time</span><strong>{{ $designations->where('session_time_limit', '!=', 30)->count() }}</strong><small>Configured</small></div></div></section>
            <section class="panel"><div class="panel-head"><h2>Recently Added</h2><a href="#">View all</a></div>@foreach($designations->take(3) as $designation)<div class="mini-person"><span class="tiny-avatar teal">{{ substr($designation->designation, 0, 1) }}</span><span><strong>{{ $designation->designation }}</strong><small>{{ $designation->session_time_limit }} minutes</small></span></div>@endforeach</section>
        </aside>
    </div>

    <div class="modal fade add-user-modal" id="addDesignationModal" tabindex="-1" aria-labelledby="addDesignationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <div class="modal-header"><h2 class="modal-title" id="addDesignationModalLabel">Add Designation</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form action="{{ route('settings.designations.store') }}" method="post">
                @csrf
                <div class="modal-body"><div class="add-user-grid">
                    <label class="wide">Designation<input type="text" name="designation" value="{{ old('designation') }}" maxlength="30" placeholder="Enter designation" required></label>
                    <label class="wide">Session Time Limit<input type="number" name="session_time_limit" value="{{ old('session_time_limit', 30) }}" min="1" placeholder="Enter minutes" required></label>
                </div></div>
                <div class="modal-footer"><button type="button" class="modal-cancel" data-bs-dismiss="modal">Cancel</button><button type="submit" class="modal-submit">Add Designation</button></div>
            </form>
        </div></div>
    </div>
@endsection
