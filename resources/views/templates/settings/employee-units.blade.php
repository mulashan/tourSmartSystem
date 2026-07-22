@extends('templates.app')

@php
    $employeeUnits = collect($employeeUnits ?? []);
    $departments = collect($departments ?? []);
    $employees = collect($employees ?? []);
@endphp

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <section class="page-hero">
        <div><h1>Employee Units</h1><p>Manage employee units, department ownership, and optional employee assignment.</p></div>
        <div class="hero-actions"><button class="outline-btn"><i class="bi bi-download"></i> Export</button><button type="button" class="nice-action" data-bs-toggle="modal" data-bs-target="#addEmployeeUnitModal"><i class="bi bi-plus"></i> Add Employee Unit</button></div>
    </section>

    <div class="content-split">
        <section class="panel table-panel">
            <div class="tabs-toolbar">
                <div class="soft-tabs"><button class="active">All <span>{{ $employeeUnits->count() }}</span></button><button>Departments <span>{{ $departments->count() }}</span></button><button>Assigned <span>{{ $employeeUnits->whereNotNull('Employee_ID')->count() }}</span></button><button>Unassigned <span>{{ $employeeUnits->whereNull('Employee_ID')->count() }}</span></button></div>
                <div class="toolbar-search"><i class="bi bi-search"></i><input placeholder="Search unit, department, employee..."><button><i class="bi bi-sliders"></i> Department</button></div>
            </div>
            <table class="nice-table directory-table">
                <thead><tr><th><input type="checkbox"></th><th>Unit</th><th>Department</th><th>Employee</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($employeeUnits as $unit)
                        @php
                            $department = $departments->firstWhere('Department_ID', $unit->Department_ID);
                            $employee = $employees->firstWhere('Employee_ID', $unit->Employee_ID);
                        @endphp
                        <tr>
                            <td><input type="checkbox"></td>
                            <td><span class="tiny-avatar {{ $loop->iteration % 2 ? 'teal' : 'indigo' }}">{{ substr($unit->unit_name, 0, 1) }}</span><strong>{{ $unit->unit_name }}</strong><small>Unit #{{ $unit->id }}</small></td>
                            <td>{{ $department->Department_Name ?? 'Not assigned' }}</td>
                            <td>{{ $employee->Employee_Name ?? 'Not assigned' }}</td>
                            <td><span class="state-dot active"></span>Active</td>
                            <td>{{ optional($unit->created_at)->format('M j, Y') }}</td>
                            <td class="row-actions"><i class="bi bi-eye"></i><i class="bi bi-pencil"></i><i class="bi bi-three-dots"></i></td>
                        </tr>
                    @endforeach
                    @if($employeeUnits->isEmpty())
                        <tr><td colspan="7">No employee units added yet.</td></tr>
                    @endif
                </tbody>
            </table>
        </section>

        <aside class="side-stack">
            <section class="panel"><h2>Unit Snapshot</h2><div class="snapshot-grid"><div><span>Total</span><strong>{{ $employeeUnits->count() }}</strong><small>Saved units</small></div><div class="green-bg"><span>Assigned</span><strong>{{ $employeeUnits->whereNotNull('Employee_ID')->count() }}</strong><small>With employee</small></div><div class="amber-bg"><span>Departments</span><strong>{{ $departments->count() }}</strong><small>Available</small></div><div class="red-bg"><span>Unassigned</span><strong>{{ $employeeUnits->whereNull('Employee_ID')->count() }}</strong><small>Open units</small></div></div></section>
            <section class="panel"><h2>Department Coverage</h2>@foreach($departments->take(3) as $department)<div class="target-row {{ $loop->iteration === 1 ? 'redbar' : ($loop->iteration === 2 ? 'amber' : 'violet') }}"><span>{{ $department->Department_Name }}</span><strong>{{ $employeeUnits->where('Department_ID', $department->Department_ID)->count() }}</strong><div><i style="width:{{ $employeeUnits->count() ? min(($employeeUnits->where('Department_ID', $department->Department_ID)->count() / $employeeUnits->count()) * 100, 100) : 0 }}%"></i></div></div>@endforeach</section>
            <section class="panel"><div class="panel-head"><h2>Recently Added</h2><a href="#">View all</a></div>@foreach($employeeUnits->take(3) as $unit)<div class="mini-person"><span class="tiny-avatar teal">{{ substr($unit->unit_name, 0, 1) }}</span><span><strong>{{ $unit->unit_name }}</strong><small>{{ optional($unit->created_at)->format('M j, Y') }}</small></span></div>@endforeach</section>
        </aside>
    </div>

    <div class="modal fade add-user-modal" id="addEmployeeUnitModal" tabindex="-1" aria-labelledby="addEmployeeUnitModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <div class="modal-header"><h2 class="modal-title" id="addEmployeeUnitModalLabel">Add Employee Unit</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form action="{{ route('settings.employee-units.store') }}" method="post">
                @csrf
                <div class="modal-body"><div class="add-user-grid">
                    <label class="wide">Unit Name<input type="text" name="unit_name" value="{{ old('unit_name') }}" placeholder="Enter employee unit name" required></label>
                    <label class="wide">Department<select name="Department_ID" required><option selected disabled value="">Select department...</option>@foreach($departments as $department)<option value="{{ $department->Department_ID }}" @selected((string) old('Department_ID') === (string) $department->Department_ID)>{{ $department->Department_Name }}</option>@endforeach</select></label>
                    <label class="wide">Employee<select name="Employee_ID"><option value="">No employee assigned</option>@foreach($employees as $employee)<option value="{{ $employee->Employee_ID }}" @selected((string) old('Employee_ID') === (string) $employee->Employee_ID)>{{ $employee->Employee_Name }}</option>@endforeach</select></label>
                </div></div>
                <div class="modal-footer"><button type="button" class="modal-cancel" data-bs-dismiss="modal">Cancel</button><button type="submit" class="modal-submit">Add Employee Unit</button></div>
            </form>
        </div></div>
    </div>
@endsection
