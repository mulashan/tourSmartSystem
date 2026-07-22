@extends('templates.app')

@php
    $employees = collect($employees ?? []);
    $branches = collect($branches ?? []);
    $departments = collect($departments ?? []);
    $userTypes = collect($userTypes ?? []);
    $totalEmployees = $employees->count();
    $activeEmployees = $employees->where('status', 'At Work')->count();
    $systemReady = $userTypes->count();
@endphp

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <section class="page-hero">
        <div><h1>Employee Directory</h1><p>Manage employee records, department placement, branch assignment, and optional system access.</p></div>
        <div class="hero-actions"><button class="outline-btn"><i class="bi bi-download"></i> Export</button><button type="button" class="nice-action" data-bs-toggle="modal" data-bs-target="#addEmployeeModal"><i class="bi bi-plus"></i> Add Employee</button></div>
    </section>

    <div class="content-split">
        <section class="panel table-panel">
            <div class="tabs-toolbar">
                <div class="soft-tabs"><button class="active">All <span>{{ $totalEmployees }}</span></button><button>At Work <span>{{ $activeEmployees }}</span></button><button>Departments <span>{{ $departments->count() }}</span></button><button>User Types <span>{{ $systemReady }}</span></button></div>
                <div class="toolbar-search"><i class="bi bi-search"></i><input placeholder="Search employee, phone, title..."><button><i class="bi bi-sliders"></i> Status</button></div>
            </div>
            <table class="nice-table directory-table">
                <thead><tr><th><input type="checkbox"></th><th>Employee</th><th>Title</th><th>Department</th><th>Branch</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($employees as $employee)
                        <tr>
                            <td><input type="checkbox"></td>
                            <td><span class="tiny-avatar {{ $employee['tone'] }}">{{ substr($employee['name'], 0, 1) }}</span><strong>{{ $employee['name'] }}</strong><small>{{ $employee['number'] }} | {{ $employee['phone'] }}</small></td>
                            <td><span class="role-pill manager"><i class="bi bi-person-badge"></i> {{ $employee['title'] }}</span></td>
                            <td>{{ $employee['department'] }}</td>
                            <td>{{ $employee['branch'] }}</td>
                            <td><span class="state-dot {{ strtolower($employee['account_status']) }}"></span>{{ $employee['status'] }}</td>
                            <td class="row-actions"><i class="bi bi-eye"></i><i class="bi bi-pencil"></i><i class="bi bi-three-dots"></i></td>
                        </tr>
                    @endforeach
                    @if($employees->isEmpty())
                        <tr>
                            <td colspan="7">No employees added yet.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <div class="table-footer"><span>Showing {{ $totalEmployees }} of {{ $totalEmployees }} employees</span><div class="pager"><button disabled><i class="bi bi-chevron-left"></i></button><button class="active">1</button><button><i class="bi bi-chevron-right"></i></button></div></div>
        </section>

        <aside class="side-stack">
            <section class="panel"><h2>Employee Snapshot</h2><div class="snapshot-grid"><div><span>Total</span><strong>{{ $totalEmployees }}</strong><small>Saved employees</small></div><div class="green-bg"><span>At Work</span><strong>{{ $activeEmployees }}</strong><small>Currently active</small></div><div class="amber-bg"><span>Departments</span><strong>{{ $departments->count() }}</strong><small>Available</small></div><div class="red-bg"><span>Branches</span><strong>{{ $branches->count() }}</strong><small>Available</small></div></div></section>
            <section class="panel"><h2>Department Coverage</h2>@foreach($departments->take(3) as $department)<div class="target-row {{ $loop->iteration === 1 ? 'redbar' : ($loop->iteration === 2 ? 'amber' : 'violet') }}"><span>{{ $department->Department_Name }}</span><strong>{{ $employees->where('department', $department->Department_Name)->count() }}</strong><div><i style="width:{{ $totalEmployees ? min(($employees->where('department', $department->Department_Name)->count() / $totalEmployees) * 100, 100) : 0 }}%"></i></div></div>@endforeach</section>
            <section class="panel"><div class="panel-head"><h2>Recently Added</h2><a href="#">View all</a></div>@foreach($employees->take(3) as $employee)<div class="mini-person"><span class="tiny-avatar {{ $employee['tone'] }}">{{ substr($employee['name'],0,1) }}</span><span><strong>{{ $employee['name'] }}</strong><small>{{ $employee['title'] }}</small></span></div>@endforeach</section>
        </aside>
    </div>

    <div class="modal fade add-user-modal" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="addEmployeeModalLabel">Add New Employee</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('employees.store') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="add-user-grid">
                            <label class="wide">Employee Name
                                <input type="text" name="employee_name" value="{{ old('employee_name') }}" placeholder="Enter full employee name" required>
                            </label>
                            <label>Employee Number
                                <input type="text" name="employee_number" value="{{ old('employee_number') }}" placeholder="Enter employee number" required>
                            </label>
                            <label>Check Number
                                <input type="text" name="employee_check_number" value="{{ old('employee_check_number') }}" placeholder="Enter check number" required>
                            </label>
                            <label>Employee Type
                                <input type="text" name="employee_type" value="{{ old('employee_type') }}" placeholder="Permanent, Contract..." required>
                            </label>
                            <label>Employee Title
                                <input type="text" name="employee_title" value="{{ old('employee_title') }}" placeholder="Enter employee title" required>
                            </label>
                            <label>Job Code
                                <input type="text" name="employee_job_code" value="{{ old('employee_job_code') }}" placeholder="Enter job code" required>
                            </label>
                            <label>Phone Number
                                <input type="text" name="phone_number" value="{{ old('phone_number') }}" placeholder="Used as username if system user" required>
                            </label>
                            <label>Date of Birth
                                <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}">
                            </label>
                            <label>Gender
                                <select name="gender">
                                    <option selected disabled value="">Select gender...</option>
                                    <option value="Male" @selected(old('gender') === 'Male')>Male</option>
                                    <option value="Female" @selected(old('gender') === 'Female')>Female</option>
                                </select>
                            </label>
                            <label>National ID
                                <input type="text" name="national_id" value="{{ old('national_id') }}" placeholder="Enter national ID">
                            </label>
                            <label>Physical Address
                                <input type="text" name="physical_address" value="{{ old('physical_address') }}" placeholder="Enter physical address">
                            </label>
                            <label class="wide">Branch
                                <select name="branch_id" required>
                                    <option selected disabled value="">Select branch...</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->Branch_ID }}" @selected((string) old('branch_id') === (string) $branch->Branch_ID)>{{ $branch->Branch_Name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="wide">Department
                                <select name="department_id">
                                    <option value="">No department</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->Department_ID }}" @selected((string) old('department_id') === (string) $department->Department_ID)>{{ $department->Department_Name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>Account Status
                                <select name="account_status" required>
                                    <option value="active" @selected(old('account_status', 'active') === 'active')>Active</option>
                                    <option value="inactive" @selected(old('account_status') === 'inactive')>Inactive</option>
                                </select>
                            </label>
                            <label>Employee Status
                                <select name="employee_status" required>
                                    <option value="At Work" @selected(old('employee_status', 'At Work') === 'At Work')>At Work</option>
                                    <option value="On Leave" @selected(old('employee_status') === 'On Leave')>On Leave</option>
                                    <option value="Suspended" @selected(old('employee_status') === 'Suspended')>Suspended</option>
                                </select>
                            </label>
                            <label>Expire Date
                                <input type="date" name="expire_date" value="{{ old('expire_date') }}">
                            </label>
                            <label>Photo
                                <input type="file" name="photo" accept="image/*">
                            </label>
                            <label>Included In Payroll
                                <select name="included_in_payroll" required>
                                    <option value="no" @selected(old('included_in_payroll', 'no') === 'no')>No</option>
                                    <option value="yes" @selected(old('included_in_payroll') === 'yes')>Yes</option>
                                </select>
                            </label>
                            <label>MSD User
                                <select name="msd_user" required>
                                    <option value="no" @selected(old('msd_user', 'no') === 'no')>No</option>
                                    <option value="yes" @selected(old('msd_user') === 'yes')>Yes</option>
                                </select>
                            </label>
                            <label>GPortal User
                                <select name="gportal_user" required>
                                    <option value="no" @selected(old('gportal_user', 'no') === 'no')>No</option>
                                    <option value="yes" @selected(old('gportal_user') === 'yes')>Yes</option>
                                </select>
                            </label>
                            <label class="modal-check wide">
                                <input type="checkbox" name="create_system_user" value="1" id="createSystemUser" @checked(old('create_system_user'))>
                                <span>Create system user account</span>
                            </label>
                            <div class="system-user-fields wide" id="systemUserFields">
                                <div class="add-user-grid">
                                    <label>User Type
                                        <select name="privilege_id">
                                            <option selected disabled value="">Select user type...</option>
                                            @foreach($userTypes as $type)
                                                <option value="{{ $type->id }}" @selected((string) old('privilege_id') === (string) $type->id)>{{ $type->privilege_name }}</option>
                                            @endforeach
                                    </select>
                                </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="modal-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="modal-submit">Add Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkbox = document.getElementById('createSystemUser');
            const fields = document.getElementById('systemUserFields');

            if (!checkbox || !fields) {
                return;
            }

            const syncFields = () => {
                fields.style.display = checkbox.checked ? 'block' : 'none';
            };

            checkbox.addEventListener('change', syncFields);
            syncFields();
        });
    </script>
@endsection
