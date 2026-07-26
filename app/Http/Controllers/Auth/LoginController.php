<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use App\Models\UserPrivilege;
use App\Models\UserTypeMenuPermission;
use App\Models\UserMenuPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function validateLogin(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'pswd' => 'required',
        ]);

        $username = $request->username;
        $password = $request->pswd;

        // Demo account bypasses branch selection entirely — not a real user row.
        if ($username == 'demo@example.com' && $password == '12345') {
            $request->session()->put([
                'logged_in'        => true,
                'user_id'          => 1,
                'db_id'            => 1,
                'active_branch_id' => 1,
                'active_branch_name' => 'Head Office',
                'institution_name' => 'NiceAdmin',
                'first_name'       => 'John',
                'last_name'        => 'Doe',
                'user_name'        => 'John Doe',
                'user_email'       => 'demo@example.com',
                'user_role'        => 'Product Admin',
                'user_initial'     => 'J',
                'user_photo'       => '',
                'privilege_id'      => null,
                'allowed_menu_keys' => [],
                'permission_bypass' => true,
            ]);

            return redirect('/dashboard');
        }

        $user = User::where('email', $username)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return back()->with('error', 'Invalid username or password');
        }

        $branches = $user->branches()->orderBy('Branch_Name')->get();

        if ($branches->isEmpty()) {
            return back()->with('error', 'Your account has no Branch assigned. Contact an administrator.');
        }

        // Stash verified identity only — full session isn't populated until a branch is chosen.
        $request->session()->put([
            'pending_login_user_id' => $user->id,
            'pending_login_redirect' => $request->input('redirect'),
        ]);

        if ($branches->count() === 1) {
            return $this->completeLogin($request, $user, $branches->first());
        }

        return redirect()->route('login.select-branch');
    }

    public function selectBranchForm(Request $request)
    {
        $userId = $request->session()->get('pending_login_user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);

        if (! $user) {
            $request->session()->forget('pending_login_user_id');
            return redirect()->route('login');
        }

        return view('templates.auth.select_branch', [
            'userName' => $user->name ?: $user->email,
            'branches' => $user->branches()->orderBy('Branch_Name')->get(),
        ]);
    }

    public function selectBranchSubmit(Request $request)
    {
        $userId = $request->session()->get('pending_login_user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);

        if (! $user) {
            $request->session()->forget('pending_login_user_id');
            return redirect()->route('login');
        }

        $request->validate(['branch_id' => 'required|integer']);

        $branch = $user->branches()->where('tbl_branches.Branch_ID', $request->branch_id)->first();

        if (! $branch) {
            return back()->with('error', 'You are not assigned to that Branch.');
        }

        return $this->completeLogin($request, $user, $branch);
    }

    private function completeLogin(Request $request, User $user, Branch $branch)
    {
        $role = UserPrivilege::find($user->privilege_id);
        $roleName = $role?->privilege_name ?? 'User';
        $isAdmin = in_array(strtolower($roleName), ['admin', 'administrator', 'product admin'], true)
            || (int) ($role?->access_level_id ?? 0) >= 9;

        $fullName = trim($user->name ?? '');
        [$firstName, $lastName] = array_pad(explode(' ', $fullName, 2), 2, '');

        $hasAnyMenuPermissions = UserTypeMenuPermission::query()->exists();

        $allowedMenuKeys = UserTypeMenuPermission::query()
            ->where('privilege_id', $user->privilege_id)
            ->where('can_access', true)
            ->pluck('menu_key')
            ->merge(
                UserMenuPermission::query()
                    ->where('user_id', $user->id)
                    ->where('can_access', true)
                    ->pluck('menu_key')
            )
            ->push('dashboard')
            ->unique()
            ->values()
            ->all();

        $redirect = $request->session()->pull('pending_login_redirect');
        $request->session()->forget('pending_login_user_id');

        $request->session()->put([
            'logged_in'        => true,
            'user_id'          => $user->id,
            'db_id'            => $branch->Branch_ID,
            'active_branch_id' => $branch->Branch_ID,
            'active_branch_name' => $branch->Branch_Name,
            'institution_name' => 'NiceAdmin',
            'first_name'       => $firstName,
            'last_name'        => $lastName,
            'user_name'        => $fullName ?: $user->email,
            'user_email'       => $user->email,
            'user_role'        => $roleName,
            'user_initial'     => strtoupper(substr($fullName ?: $user->email, 0, 1)),
            'user_photo'       => $user->photo ?? '',
            'privilege_id'      => $user->privilege_id,
            'allowed_menu_keys' => $allowedMenuKeys,
            'permission_bypass' => $isAdmin || ! $hasAnyMenuPermissions,
        ]);

        return redirect($redirect ?: '/dashboard');
    }
}