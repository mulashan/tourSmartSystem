<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchDepartmentController extends Controller
{
    public function list(Request $request): View
    {
        $items = Department::with(['branch', 'departmentNature'])
            ->when($request->query('search'), fn ($q, $search) => $q->where('Department_Name', 'like', "%{$search}%"))
            ->orderBy('Department_Name')
            ->get();

        return view('templates.settings.other_settings.partials.branch_department_table', compact('items'));
    }

    public function store(Request $request): JsonResponse
    {
        $department = Department::create($this->validated($request));

        return response()->json(['success' => true, 'item' => $department->load(['branch', 'departmentNature'])]);
    }

    public function update(Request $request, Department $department): JsonResponse
    {
        $department->update($this->validated($request, $department->Department_ID));

        return response()->json(['success' => true, 'item' => $department->load(['branch', 'departmentNature'])]);
    }

    public function destroy(Department $department): JsonResponse
    {
        $department->delete();

        return response()->json(['success' => true]);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'Department_Name' => 'required|string|max:100|unique:tbl_department,Department_Name,' . ($ignoreId ?? 'NULL') . ',Department_ID',
            'Branch_ID' => 'required|integer|exists:tbl_branches,Branch_ID',
            'department_nature_id' => 'required|integer|exists:tbl_department_nature,id',
        ]);
    }
}