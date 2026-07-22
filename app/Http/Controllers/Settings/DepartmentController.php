<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $branches = Branch::query()->orderBy('Branch_Name')->get();
        $departments = Department::query()
            ->latest('Department_ID')
            ->get()
            ->map(fn (Department $department): array => $this->departmentRow($department, $branches));

        return $this->nicePage('templates.settings.department', 'departments.list', [
            'departments' => $departments,
            'branches' => $branches,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'department_name' => ['required', 'string', 'max:100', 'unique:tbl_department,Department_Name'],
            'department_location' => ['required', 'string', 'max:100'],
            'branch_id' => ['required', 'integer', 'exists:tbl_branches,Branch_ID'],
            'department_status' => ['required', 'string', 'max:15'],
            'status' => ['required', 'integer', 'in:0,1'],
        ]);

        Department::create([
            'Department_Name' => $validated['department_name'],
            'Department_Location' => $validated['department_location'],
            'Branch_ID' => $validated['branch_id'],
            'Department_Status' => $validated['department_status'],
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('departments.list')
            ->with('success', 'Department saved successfully.');
    }

    private function departmentRow(Department $department, $branches): array
    {
        $branch = $branches->firstWhere('Branch_ID', $department->Branch_ID);
        $tones = ['teal', 'tan', 'slate', 'indigo'];

        return [
            'name' => $department->Department_Name,
            'code' => 'DEP-' . str_pad((string) $department->Department_ID, 3, '0', STR_PAD_LEFT),
            'branch' => $branch->Branch_Name ?? 'Not assigned',
            'status' => $department->Department_Status ?: 'active',
            'location' => $department->Department_Location,
            'state' => (int) $department->status === 1 ? 'Enabled' : 'Disabled',
            'tone' => $tones[$department->Department_ID % count($tones)],
        ];
    }
}
