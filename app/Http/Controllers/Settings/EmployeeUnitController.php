<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeUnit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeUnitController extends Controller
{
    public function index(): View|RedirectResponse
    {
        return $this->nicePage('templates.settings.employee-units', 'settings.employee-units', [
            'employeeUnits' => EmployeeUnit::query()->latest('id')->get(),
            'departments' => Department::query()->orderBy('Department_Name')->get(),
            'employees' => Employee::query()->orderBy('Employee_Name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'unit_name' => ['required', 'string', 'max:255'],
            'Department_ID' => ['required', 'integer', 'exists:tbl_department,Department_ID'],
            'Employee_ID' => ['nullable', 'integer', 'exists:tbl_employee,Employee_ID'],
        ]);

        EmployeeUnit::create($validated);

        return redirect()->route('settings.employee-units')->with('success', 'Employee unit saved successfully.');
    }
}
