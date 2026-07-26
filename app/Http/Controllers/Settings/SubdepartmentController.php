<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Subdepartment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubdepartmentController extends Controller
{
    public function list(Request $request): View
    {
        $items = Subdepartment::with(['department.branch', 'department.departmentNature'])
            ->when($request->query('search'), fn ($q, $search) => $q->where('Subdepartment_Name', 'like', "%{$search}%"))
            ->orderBy('Subdepartment_Name')
            ->get();

        return view('templates.settings.other_settings.partials.subdepartment_table', compact('items'));
    }

    public function store(Request $request): JsonResponse
    {
        $subdepartment = Subdepartment::create($this->validated($request));

        return response()->json(['success' => true, 'item' => $subdepartment->load('department.departmentNature')]);
    }

    public function update(Request $request, Subdepartment $subdepartment): JsonResponse
    {
        $subdepartment->update($this->validated($request, $subdepartment->Subdepartment_ID, $subdepartment->Department_ID));

        return response()->json(['success' => true, 'item' => $subdepartment->load('department.departmentNature')]);
    }

    public function destroy(Subdepartment $subdepartment): JsonResponse
    {
        $subdepartment->delete();

        return response()->json(['success' => true]);
    }

    private function validated(Request $request, ?int $ignoreId = null, ?int $currentDeptId = null): array
    {
        $departmentId = $request->input('Department_ID', $currentDeptId);

        return $request->validate([
            'Subdepartment_Name' => [
                'required', 'string', 'max:100',
                'unique:tbl_subdepartment,Subdepartment_Name,' . ($ignoreId ?? 'NULL') . ',Subdepartment_ID,Department_ID,' . $departmentId,
            ],
            'Department_ID' => 'required|integer|exists:tbl_department,Department_ID',
        ]);
    }
}