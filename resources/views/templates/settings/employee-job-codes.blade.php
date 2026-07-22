@extends('templates.app')

@php
    $jobCodes = collect($jobCodes ?? []);
@endphp

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <section class="page-hero">
        <div><h1>Employee Job Codes</h1><p>Manage job code records used when registering and assigning employees.</p></div>
        <div class="hero-actions"><button class="outline-btn"><i class="bi bi-download"></i> Export</button><button type="button" class="nice-action" data-bs-toggle="modal" data-bs-target="#addJobCodeModal"><i class="bi bi-plus"></i> Add Job Code</button></div>
    </section>

    <div class="content-split">
        <section class="panel table-panel">
            <div class="tabs-toolbar">
                <div class="soft-tabs"><button class="active">All <span>{{ $jobCodes->count() }}</span></button><button>Active <span>{{ $jobCodes->count() }}</span></button><button>Pending <span>0</span></button><button>Inactive <span>0</span></button></div>
                <div class="toolbar-search"><i class="bi bi-search"></i><input placeholder="Search job code..."><button><i class="bi bi-sliders"></i> Filter</button></div>
            </div>
            <table class="nice-table directory-table">
                <thead><tr><th><input type="checkbox"></th><th>Job Code</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($jobCodes as $jobCode)
                        <tr>
                            <td><input type="checkbox"></td>
                            <td><span class="tiny-avatar {{ $loop->iteration % 2 ? 'teal' : 'indigo' }}">{{ substr($jobCode->job_code, 0, 1) }}</span><strong>{{ $jobCode->job_code }}</strong><small>Code #{{ $jobCode->employee_job_code_id }}</small></td>
                            <td><span class="state-dot active"></span>Active</td>
                            <td>{{ optional($jobCode->created_at)->format('M j, Y') }}</td>
                            <td class="row-actions"><i class="bi bi-eye"></i><i class="bi bi-pencil"></i><i class="bi bi-three-dots"></i></td>
                        </tr>
                    @endforeach
                    @if($jobCodes->isEmpty())
                        <tr><td colspan="5">No employee job codes added yet.</td></tr>
                    @endif
                </tbody>
            </table>
        </section>

        <aside class="side-stack">
            <section class="panel"><h2>Job Code Snapshot</h2><div class="snapshot-grid"><div><span>Total</span><strong>{{ $jobCodes->count() }}</strong><small>Saved codes</small></div><div class="green-bg"><span>Active</span><strong>{{ $jobCodes->count() }}</strong><small>Ready to use</small></div></div></section>
            <section class="panel"><div class="panel-head"><h2>Recently Added</h2><a href="#">View all</a></div>@foreach($jobCodes->take(3) as $jobCode)<div class="mini-person"><span class="tiny-avatar teal">{{ substr($jobCode->job_code, 0, 1) }}</span><span><strong>{{ $jobCode->job_code }}</strong><small>{{ optional($jobCode->created_at)->format('M j, Y') }}</small></span></div>@endforeach</section>
        </aside>
    </div>

    <div class="modal fade add-user-modal" id="addJobCodeModal" tabindex="-1" aria-labelledby="addJobCodeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <div class="modal-header"><h2 class="modal-title" id="addJobCodeModalLabel">Add Job Code</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form action="{{ route('settings.employee-job-codes.store') }}" method="post">
                @csrf
                <div class="modal-body"><div class="add-user-grid"><label class="wide">Job Code<input type="text" name="job_code" value="{{ old('job_code') }}" placeholder="Enter employee job code" required></label></div></div>
                <div class="modal-footer"><button type="button" class="modal-cancel" data-bs-dismiss="modal">Cancel</button><button type="submit" class="modal-submit">Add Job Code</button></div>
            </form>
        </div></div>
    </div>
@endsection
