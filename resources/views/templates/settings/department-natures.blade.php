@extends('templates.app')

@php
    $departmentNatures = collect($departmentNatures ?? []);
@endphp

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <section class="page-hero">
        <div><h1>Department Nature</h1><p>Manage department nature records for HR department setup.</p></div>
        <div class="hero-actions"><button class="outline-btn"><i class="bi bi-download"></i> Export</button><button type="button" class="nice-action" data-bs-toggle="modal" data-bs-target="#addDepartmentNatureModal"><i class="bi bi-plus"></i> Add Department Nature</button></div>
    </section>

    <div class="content-split">
        <section class="panel table-panel">
            <div class="tabs-toolbar">
                <div class="soft-tabs"><button class="active">All <span>{{ $departmentNatures->count() }}</span></button><button>Active <span>{{ $departmentNatures->count() }}</span></button><button>Pending <span>0</span></button><button>Inactive <span>0</span></button></div>
                <div class="toolbar-search"><i class="bi bi-search"></i><input placeholder="Search department nature..."><button><i class="bi bi-sliders"></i> Filter</button></div>
            </div>
            <table class="nice-table directory-table">
                <thead><tr><th><input type="checkbox"></th><th>Department Nature</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($departmentNatures as $nature)
                        <tr>
                            <td><input type="checkbox"></td>
                            <td><span class="tiny-avatar {{ $loop->iteration % 2 ? 'teal' : 'indigo' }}">{{ substr($nature->department_nature, 0, 1) }}</span><strong>{{ $nature->department_nature }}</strong><small>Nature #{{ $nature->id }}</small></td>
                            <td><span class="state-dot active"></span>Active</td>
                            <td>{{ optional($nature->created_at)->format('M j, Y') }}</td>
                            <td class="row-actions"><i class="bi bi-eye"></i><i class="bi bi-pencil"></i><i class="bi bi-three-dots"></i></td>
                        </tr>
                    @endforeach
                    @if($departmentNatures->isEmpty())
                        <tr><td colspan="5">No department nature records added yet.</td></tr>
                    @endif
                </tbody>
            </table>
        </section>

        <aside class="side-stack">
            <section class="panel"><h2>Nature Snapshot</h2><div class="snapshot-grid"><div><span>Total</span><strong>{{ $departmentNatures->count() }}</strong><small>Saved records</small></div><div class="green-bg"><span>Active</span><strong>{{ $departmentNatures->count() }}</strong><small>Ready to use</small></div></div></section>
            <section class="panel"><div class="panel-head"><h2>Recently Added</h2><a href="#">View all</a></div>@foreach($departmentNatures->take(3) as $nature)<div class="mini-person"><span class="tiny-avatar teal">{{ substr($nature->department_nature, 0, 1) }}</span><span><strong>{{ $nature->department_nature }}</strong><small>{{ optional($nature->created_at)->format('M j, Y') }}</small></span></div>@endforeach</section>
        </aside>
    </div>

    <div class="modal fade add-user-modal" id="addDepartmentNatureModal" tabindex="-1" aria-labelledby="addDepartmentNatureModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <div class="modal-header"><h2 class="modal-title" id="addDepartmentNatureModalLabel">Add Department Nature</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form action="{{ route('settings.department-natures.store') }}" method="post">
                @csrf
                <div class="modal-body"><div class="add-user-grid"><label class="wide">Department Nature<input type="text" name="department_nature" value="{{ old('department_nature') }}" placeholder="Enter department nature" required></label></div></div>
                <div class="modal-footer"><button type="button" class="modal-cancel" data-bs-dismiss="modal">Cancel</button><button type="submit" class="modal-submit">Add Department Nature</button></div>
            </form>
        </div></div>
    </div>
@endsection
