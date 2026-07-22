<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\UserPrivilege;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserTypeController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $userTypes = UserPrivilege::query()
            ->latest()
            ->get();

        return $this->nicePage('templates.users.types', 'users.types', [
            'userTypes' => $userTypes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'privilege_name' => ['required', 'string', 'max:255', 'unique:tbl_users_privileges,privilege_name'],
            'privilege_desc' => ['required', 'string', 'max:255'],
            'access_level_id' => ['required', 'integer', 'min:1'],
            'priv_status' => ['required', 'boolean'],
        ]);

        UserPrivilege::create($validated);

        return redirect()
            ->route('users.types')
            ->with('success', 'User type saved successfully.');
    }
}
