<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use App\Models\UserPrivilege;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $users = User::query()
            ->latest()
            ->get();

        return $this->nicePage('templates.users.list', 'users.list', [
            'users' => $users,
            'branches' => Branch::query()->orderBy('Branch_Name')->get(),
            'userTypes' => UserPrivilege::query()->where('priv_status', true)->orderBy('privilege_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'branch_id' => ['required', 'integer'],
            'privilege_id' => ['required', 'integer'],
            'email' => ['required', 'string', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()
            ->route('users.list')
            ->with('success', 'User saved successfully.');
    }
}
