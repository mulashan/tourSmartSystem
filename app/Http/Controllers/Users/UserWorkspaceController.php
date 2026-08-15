<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\ApprovalPermission;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Subdepartment;
use App\Models\User;
use App\Models\UserPrivilege;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use App\Models\UserTypeMenuPermission;
use App\Models\UserMenuPermission;
use App\Models\UserWorkshopPermission;

class UserWorkspaceController extends Controller
{
    private array $tabs = ['edit-employee', 'assign-branch', 'assign-subdepartment', 'assign-approval-permission', 'assign-system-permission', 'assign-workshop-permission'];

    public function show(User $user, string $tab = 'edit-employee'): View|RedirectResponse
    {
        abort_unless(in_array($tab, $this->tabs, true), 404);

        return $this->nicePage('templates.users.workspace', 'users.list', array_merge(
            $this->baseData($user),
            ['user' => $user, 'tab' => $tab],
            $this->tabData($user, $tab)
        ));
    }

    public function tabContent(User $user, string $tab): View
    {
        abort_unless(in_array($tab, $this->tabs, true), 404);

        return view('templates.users.tabs.' . $tab, array_merge(
            $this->baseData($user),
            ['user' => $user, 'tab' => $tab],
            $this->tabData($user, $tab)
        ));
    }

    private function baseData(User $user): array
    {
        return [
            'userTypes' => UserPrivilege::orderBy('privilege_name')->get(),
            'branches' => $user->branches()->orderBy('Branch_Name')->get(),
        ];
    }

    private function tabData(User $user, string $tab): array
    {
        if ($tab === 'edit-employee') {
            return ['userTypes' => UserPrivilege::orderBy('privilege_name')->get()];
        }

        if ($tab === 'assign-branch') {
            $user->load('branches');
            $assignedIds = $user->branches->pluck('Branch_ID')->all();

            return [
                'assignedBranches' => $user->branches,
                'availableBranches' => Branch::whereNotIn('Branch_ID', $assignedIds)->orderBy('Branch_Name')->get(),
            ];
        }

        if ($tab === 'assign-subdepartment') {
            $user->load('subdepartments.department.branch');

            $branchIds = $user->branches()->pluck('Branch_ID')->all();
            $assignedIds = $user->subdepartments->pluck('Subdepartment_ID')->all();

            $availableSubdepartments = Subdepartment::with('department.branch')
                ->whereHas('department', fn ($q) => $q->whereIn('Branch_ID', $branchIds))
                ->whereNotIn('Subdepartment_ID', $assignedIds)
                ->orderBy('Subdepartment_Name')
                ->get();

            return [
                'assignedSubdepartments' => $user->subdepartments,
                'availableSubdepartments' => $availableSubdepartments,
            ];
        }

        if ($tab === 'assign-approval-permission') {
            $user->load('approvalPermissions');

            return [
                'permissions' => ApprovalPermission::orderBy('label')->get(),
                'assignedIds' => $user->approvalPermissions->pluck('id')->all(),
            ];
        }

        if ($tab === 'assign-system-permission') {
            $groupGrantedKeys = UserTypeMenuPermission::query()
                ->where('privilege_id', $user->privilege_id)
                ->where('can_access', true)
                ->pluck('menu_key')
                ->all();

            $individualGrantedKeys = $user->menuPermissions()->where('can_access', true)->pluck('menu_key')->all();

            return [
                'menuGroups' => $this->permissionMenus(),
                'groupGrantedKeys' => $groupGrantedKeys,
                'individualGrantedKeys' => $individualGrantedKeys,
            ];
        }

        if ($tab === 'assign-workshop-permission') {
            $user->load('workshopPermissions');

            return [
                'permissions' => $this->workshopPermissions(),
                'assignedKeys' => $user->workshopPermissions->pluck('permission_key')->all(),
            ];
        }

        return [];
    }

    public function updateEmployee(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'privilege_id' => 'required|integer|exists:tbl_users_privileges,id',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->privilege_id = $data['privilege_id'];

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()->route('users.show', [$user, 'edit-employee'])->with('success', 'Employee details updated.');
    }

    public function addBranch(Request $request, User $user): JsonResponse
    {
        $data = $request->validate(['branch_id' => 'required|integer|exists:tbl_branches,Branch_ID']);

        $user->branches()->syncWithoutDetaching([$data['branch_id']]);

        return response()->json(['success' => true]);
    }

    public function removeBranch(User $user, Branch $branch): JsonResponse
    {
        $user->branches()->detach($branch->Branch_ID);

        return response()->json(['success' => true]);
    }

    public function subdepartmentOptions(Request $request, User $user): JsonResponse
    {
        $data = $request->validate(['department_id' => 'required|integer|exists:tbl_department,Department_ID']);

        $assignedIds = $user->subdepartments->pluck('Subdepartment_ID')->all();

        $options = Subdepartment::where('Department_ID', $data['department_id'])
            ->whereNotIn('Subdepartment_ID', $assignedIds)
            ->orderBy('Subdepartment_Name')
            ->get(['Subdepartment_ID', 'Subdepartment_Name']);

        return response()->json($options);
    }

    public function addSubdepartment(Request $request, User $user): JsonResponse
    {
        $data = $request->validate(['subdepartment_id' => 'required|integer|exists:tbl_subdepartment,Subdepartment_ID']);

        $branchIds = $user->branches()->pluck('Branch_ID')->all();
        $subdepartment = Subdepartment::with('department')->findOrFail($data['subdepartment_id']);

        abort_unless(in_array($subdepartment->department?->Branch_ID, $branchIds, true), 422, 'This Sub Department does not belong to any of the user\'s assigned branches.');

        $user->subdepartments()->syncWithoutDetaching([$data['subdepartment_id']]);

        return response()->json(['success' => true]);
    }

    public function removeSubdepartment(User $user, Subdepartment $subdepartment): JsonResponse
    {
        $user->subdepartments()->detach($subdepartment->Subdepartment_ID);

        return response()->json(['success' => true]);
    }

    public function updateApprovalPermissions(Request $request, User $user): JsonResponse
    {
        $ids = $request->validate(['permission_ids' => 'array'])['permission_ids'] ?? [];

        $user->approvalPermissions()->sync($ids);

        return response()->json(['success' => true]);
    }

    public function updateMenuPermissions(Request $request, User $user): JsonResponse
    {
        $keys = $request->validate(['menu_keys' => 'array'])['menu_keys'] ?? [];

        UserMenuPermission::where('user_id', $user->id)->whereNotIn('menu_key', $keys)->delete();

        foreach ($keys as $key) {
            UserMenuPermission::updateOrCreate(
                ['user_id' => $user->id, 'menu_key' => $key],
                ['can_access' => true]
            );
        }

        return response()->json(['success' => true]);
    }

    public function updateWorkshopPermissions(Request $request, User $user): JsonResponse
    {
        $keys = $request->validate(['permission_keys' => 'array'])['permission_keys'] ?? [];
        $allowedKeys = collect($this->workshopPermissions())->pluck('key')->all();
        $keys = collect($keys)->intersect($allowedKeys)->values()->all();

        $existingPermissions = UserWorkshopPermission::where('user_id', $user->id);

        empty($keys)
            ? $existingPermissions->delete()
            : $existingPermissions->whereNotIn('permission_key', $keys)->delete();

        foreach ($keys as $key) {
            UserWorkshopPermission::updateOrCreate(
                ['user_id' => $user->id, 'permission_key' => $key],
                ['permission_key' => $key]
            );
        }

        return response()->json(['success' => true]);
    }

    private function workshopPermissions(): array
    {
        return [
            [
                'key' => 'repair_order',
                'label' => 'Repair Order',
                'description' => 'Create and manage repair orders for workshop job cards.',
            ],
            [
                'key' => 'diagnosis',
                'label' => 'Diagnosis',
                'description' => 'Record vehicle diagnosis findings and recommendations.',
            ],
            [
                'key' => 'assign_mechanics',
                'label' => 'Assign Mechanics',
                'description' => 'Assign mechanics and track mechanic work progress.',
            ],
            [
                'key' => 'record_labour',
                'label' => 'Record Labour',
                'description' => 'Record labour hours, rates and work done.',
            ],
            [
                'key' => 'issue_spare_parts',
                'label' => 'Issue Spare Parts',
                'description' => 'Issue spare parts from store stock to workshop jobs.',
            ],
            [
                'key' => 'complete_repair',
                'label' => 'Complete Repair',
                'description' => 'Mark workshop repairs as completed and ready for inspection.',
            ],
            [
                'key' => 'quality_inspection',
                'label' => 'Quality Inspection',
                'description' => 'Inspect completed repairs and approve or return them for rework.',
            ],
            [
                'key' => 'generate_invoice',
                'label' => 'Generate Invoice',
                'description' => 'Generate workshop invoices from labour and parts totals.',
            ],
            [
                'key' => 'close_job_card',
                'label' => 'Close Job Card',
                'description' => 'Close invoiced workshop job cards.',
            ],
        ];
    }
}
