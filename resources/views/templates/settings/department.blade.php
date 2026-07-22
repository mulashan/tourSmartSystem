@extends('templates.app')

@php
    $departments = collect($departments ?? []);
    $branches = collect($branches ?? []);
    $totalDepartments = $departments->count();
    $activeDepartments = $departments->where('status', 'active')->count();
    $inactiveDepartments = $departments->where('status', 'inactive')->count();
    $enabledDepartments = $departments->where('state', 'Enabled')->count();
@endphp

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <section class="page-hero">
        <div><h1>Department Directory</h1><p>Manage departments, branch ownership, locations, and operating status.</p></div>
        <div class="hero-actions"><button class="outline-btn"><i class="bi bi-download"></i> Export</button><button type="button" class="nice-action" data-bs-toggle="modal" data-bs-target="#addDepartmentModal"><i class="bi bi-plus"></i> Add Department</button></div>
    </section>

    <div class="content-split">
        <section class="panel table-panel">
            <div class="tabs-toolbar">
                <div class="soft-tabs"><button class="active">All <span>{{ $totalDepartments }}</span></button><button>Active <span>{{ $activeDepartments }}</span></button><button>Inactive <span>{{ $inactiveDepartments }}</span></button><button>Enabled <span>{{ $enabledDepartments }}</span></button></div>
                <div class="toolbar-search"><i class="bi bi-search"></i><input placeholder="Search department, branch, location..."><button><i class="bi bi-sliders"></i> Status</button></div>
            </div>
            <table class="nice-table directory-table">
                <thead><tr><th><input type="checkbox"></th><th>Department</th><th>Branch</th><th>Status</th><th>Location</th><th>State</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($departments as $department)
                        <tr>
                            <td><input type="checkbox"></td>
                            <td><span class="tiny-avatar {{ $department['tone'] }}">{{ substr($department['name'], 0, 1) }}</span><strong>{{ $department['name'] }}</strong><small>{{ $department['code'] }}</small></td>
                            <td><span class="role-pill manager"><i class="bi bi-building"></i> {{ $department['branch'] }}</span></td>
                            <td><span class="state-dot {{ strtolower($department['status']) }}"></span>{{ ucfirst($department['status']) }}</td>
                            <td>{{ $department['location'] }}</td>
                            <td>{{ $department['state'] }}</td>
                            <td class="row-actions"><i class="bi bi-eye"></i><i class="bi bi-pencil"></i><i class="bi bi-three-dots"></i></td>
                        </tr>
                    @endforeach
                    @if($departments->isEmpty())
                        <tr>
                            <td colspan="7">No departments added yet.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <div class="table-footer"><span>Showing {{ $totalDepartments }} of {{ $totalDepartments }} departments</span><div class="pager"><button disabled><i class="bi bi-chevron-left"></i></button><button class="active">1</button><button><i class="bi bi-chevron-right"></i></button></div></div>
        </section>

        <aside class="side-stack">
            <section class="panel"><h2>Department Snapshot</h2><div class="snapshot-grid"><div><span>Total</span><strong>{{ $totalDepartments }}</strong><small>Saved departments</small></div><div class="green-bg"><span>Active</span><strong>{{ $activeDepartments }}</strong><small>Operating</small></div><div class="amber-bg"><span>Branches</span><strong>{{ $branches->count() }}</strong><small>Available branches</small></div><div class="red-bg"><span>Inactive</span><strong>{{ $inactiveDepartments }}</strong><small>Needs review</small></div></div></section>
            <section class="panel"><h2>Branch Coverage</h2>@foreach($branches->take(3) as $branch)<div class="target-row {{ $loop->iteration === 1 ? 'redbar' : ($loop->iteration === 2 ? 'amber' : 'violet') }}"><span>{{ $branch->Branch_Name }}</span><strong>{{ $departments->where('branch', $branch->Branch_Name)->count() }}</strong><div><i style="width:{{ $totalDepartments ? min(($departments->where('branch', $branch->Branch_Name)->count() / $totalDepartments) * 100, 100) : 0 }}%"></i></div></div>@endforeach</section>
            <section class="panel"><div class="panel-head"><h2>Recently Added</h2><a href="#">View all</a></div>@foreach($departments->take(3) as $department)<div class="mini-person"><span class="tiny-avatar {{ $department['tone'] }}">{{ substr($department['name'],0,1) }}</span><span><strong>{{ $department['name'] }}</strong><small>{{ $department['branch'] }}</small></span></div>@endforeach</section>
        </aside>
    </div>

    <div class="modal fade add-user-modal" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="addDepartmentModalLabel">Add New Department</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('departments.store') }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="add-user-grid">
                            <label>Department Name
                                <input type="text" name="department_name" value="{{ old('department_name') }}" placeholder="Enter department name" required>
                            </label>
                            <label>Department Location
                                <input type="text" name="department_location" value="{{ old('department_location') }}" placeholder="Enter department location" required>
                            </label>
                            <label class="wide">Branch
                                <select name="branch_id" required>
                                    <option selected disabled value="">Select branch...</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->Branch_ID }}" @selected((string) old('branch_id') === (string) $branch->Branch_ID)>{{ $branch->Branch_Name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>Department Status
                                <select name="department_status" required>
                                    <option value="active" @selected(old('department_status', 'active') === 'active')>Active</option>
                                    <option value="inactive" @selected(old('department_status') === 'inactive')>Inactive</option>
                                </select>
                            </label>
                            <label>System Status
                                <select name="status" required>
                                    <option value="0" @selected(old('status', '0') === '0')>Disabled</option>
                                    <option value="1" @selected(old('status') === '1')>Enabled</option>
                                </select>
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="modal-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="modal-submit">Add Department</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
