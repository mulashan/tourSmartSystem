<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPrivilege;
use App\Models\UserTypeMenuPermission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RolesPermissionController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $userTypes = UserPrivilege::query()->orderBy('privilege_name')->get();
        $selectedType = $userTypes->firstWhere('id', (int) $request->query('type')) ?? $userTypes->first();
        $selectedKeys = collect();

        if ($selectedType) {
            $selectedKeys = UserTypeMenuPermission::query()
                ->where('privilege_id', $selectedType->id)
                ->where('can_access', true)
                ->pluck('menu_key');
        }

        return nicePage('templates.users.roles', 'users.roles', [
            'userTypes' => $userTypes,
            'selectedType' => $selectedType,
            'selectedKeys' => $selectedKeys,
            'permissionMenus' => nicePermissionMenus(),
            'userCounts' => User::query()
                ->selectRaw('privilege_id, COUNT(*) as total')
                ->groupBy('privilege_id')
                ->pluck('total', 'privilege_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'privilege_id' => ['required', 'exists:tbl_users_privileges,id'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'max:100'],
        ]);

        $allowedKeys = collect(nicePermissionMenus())
            ->pluck('items')
            ->flatten(1)
            ->pluck('key')
            ->push('dashboard')
            ->unique()
            ->values();

        $selectedKeys = collect($validated['permissions'] ?? [])
            ->push('dashboard')
            ->intersect($allowedKeys)
            ->unique()
            ->values();

        UserTypeMenuPermission::query()
            ->where('privilege_id', $validated['privilege_id'])
            ->delete();

        foreach ($selectedKeys as $menuKey) {
            UserTypeMenuPermission::create([
                'privilege_id' => $validated['privilege_id'],
                'menu_key' => $menuKey,
                'can_access' => true,
            ]);
        }

        return redirect()
            ->route('users.roles', ['type' => $validated['privilege_id']])
            ->with('success', 'Permissions saved successfully.');
    }
}
