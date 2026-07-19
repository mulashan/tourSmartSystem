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

        return nicePage('templates.users.list', 'users.list', [
            'users' => $users,
            'branches' => Branch::query()->orderBy('Branch_Name')->get(),
            'userTypes' => UserPrivilege::query()->where('priv_status', true)->orderBy('privilege_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'second_name' => ['required', 'string', 'max:255'],
            'other_names' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date'],
            'gender' => ['required', 'string', 'max:50'],
            'branch_id' => ['required', 'integer'],
            'national_id' => ['nullable', 'string', 'max:255'],
            'privilege_id' => ['required', 'integer'],
            'physical_address' => ['nullable', 'string'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()
            ->route('users.list')
            ->with('success', 'User saved successfully.');
    }
}
