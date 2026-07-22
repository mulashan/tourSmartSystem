<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Models\UserPrivilege;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $branches = Branch::query()->orderBy('Branch_Name')->get();
        $departments = Department::query()->orderBy('Department_Name')->get();
        $userTypes = UserPrivilege::query()->where('priv_status', true)->orderBy('privilege_name')->get();

        $employees = Employee::query()
            ->latest('Employee_ID')
            ->get()
            ->map(fn (Employee $employee): array => $this->employeeRow($employee, $departments));

        return $this->nicePage('templates.settings.employee', 'employees.list', [
            'employees' => $employees,
            'branches' => $branches,
            'departments' => $departments,
            'userTypes' => $userTypes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $createSystemUser = $request->boolean('create_system_user');
        $phoneRules = ['required', 'string', 'max:50'];

        if ($createSystemUser) {
            $phoneRules[] = Rule::unique('users', 'email');
        }

        $validated = $request->validate([
            'employee_name' => ['required', 'string', 'max:300'],
            'employee_type' => ['required', 'string', 'max:300'],
            'employee_number' => ['required', 'string', 'max:150', 'unique:tbl_employee,Employee_Number'],
            'employee_check_number' => ['required', 'string', 'max:19'],
            'employee_title' => ['required', 'string', 'max:150'],
            'employee_job_code' => ['required', 'string', 'max:150'],
            'branch_id' => ['required', 'integer', 'exists:tbl_branches,Branch_ID'],
            'department_id' => ['nullable', 'integer', 'exists:tbl_department,Department_ID'],
            'account_status' => ['required', 'string', 'max:40'],
            'phone_number' => $phoneRules,
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:50'],
            'national_id' => ['nullable', 'string', 'max:255'],
            'physical_address' => ['nullable', 'string'],
            'expire_date' => ['nullable', 'date'],
            'included_in_payroll' => ['required', 'string', 'in:yes,no'],
            'employee_status' => ['required', 'string', 'max:255'],
            'msd_user' => ['required', 'string', 'in:yes,no'],
            'gportal_user' => ['required', 'string', 'in:yes,no'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'create_system_user' => ['nullable', 'boolean'],
            'privilege_id' => [$createSystemUser ? 'required' : 'nullable', 'integer', 'exists:tbl_users_privileges,id'],
        ]);

        $photoPath = $request->file('photo')?->store('employees', 'public');
        $branch = Branch::query()->findOrFail($validated['branch_id']);

        DB::transaction(function () use ($validated, $photoPath, $branch, $createSystemUser): void {
            Employee::create([
                'Employee_Name' => $validated['employee_name'],
                'Employee_Type_ID' => null,
                'Employee_Type' => $validated['employee_type'],
                'Employee_Number' => $validated['employee_number'],
                'Employee_Check_Number' => $validated['employee_check_number'],
                'Employee_Title' => $validated['employee_title'],
                'Employee_Title_ID' => null,
                'Employee_Job_Code' => $validated['employee_job_code'],
                'Employee_Branch_Name' => $branch->Branch_Name,
                'Department_ID' => $validated['department_id'] ?? null,
                'Account_Status' => $validated['account_status'],
                'employee_signature' => null,
                'photo' => $photoPath,
                'Phone_Number' => $validated['phone_number'],
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'national_id' => $validated['national_id'] ?? null,
                'physical_address' => $validated['physical_address'] ?? null,
                'created_at' => now(),
                'created_by' => session('user_id'),
                'Expire_Date' => $validated['expire_date'] ?? null,
                'Unit_ID' => null,
                'Included_In_Payroll' => $validated['included_in_payroll'],
                'Employee_Status' => $validated['employee_status'],
                'Msd_User' => $validated['msd_user'],
                'gportalUser' => $validated['gportal_user'],
            ]);

            if ($createSystemUser) {
                User::create([
                    'name' => $validated['employee_name'],
                    'branch_id' => $branch->Branch_ID,
                    'privilege_id' => $validated['privilege_id'],
                    'photo' => $photoPath,
                    'email' => $validated['phone_number'],
                    'password' => Hash::make('12345'),
                ]);
            }
        });

        return redirect()
            ->route('employees.list')
            ->with('success', $createSystemUser
                ? 'Employee saved and system user account created. Default password is 12345.'
                : 'Employee saved successfully.');
    }

    private function employeeRow(Employee $employee, $departments): array
    {
        $department = $departments->firstWhere('Department_ID', $employee->Department_ID);
        $tones = ['teal', 'tan', 'slate', 'indigo'];

        return [
            'name' => $employee->Employee_Name,
            'number' => $employee->Employee_Number,
            'type' => $employee->Employee_Type,
            'title' => $employee->Employee_Title,
            'branch' => $employee->Employee_Branch_Name,
            'department' => $department->Department_Name ?? 'Not assigned',
            'phone' => $employee->Phone_Number,
            'status' => $employee->Employee_Status,
            'account_status' => $employee->Account_Status,
            'photo' => $employee->photo,
            'tone' => $tones[$employee->Employee_ID % count($tones)],
        ];
    }

}
