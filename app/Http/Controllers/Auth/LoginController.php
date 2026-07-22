<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPrivilege;
use App\Models\UserTypeMenuPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function validateLogin(Request $request)
    {
        // Validate input
        $request->validate([
            'username' => 'required',
            'pswd' => 'required',
        ]);

        $user = User::where('email', $request->username)->first();
        $username = $request->username;
        $password = $request->pswd;

        if ($username == 'demo@example.com' && $password == '12345') {

            $request->session()->put([
                'logged_in'        => true,
                'user_id'          => 1,
                'db_id'            => 1,
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

        if (! $user || ! Hash::check($password, $user->password)) {
            return back()->with('error', 'Invalid username or password');
        }

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
            ->push('dashboard')
            ->unique()
            ->values()
            ->all();

        $request->session()->put([
            'logged_in'        => true,
            'user_id'          => $user->id,
            'db_id'            => $user->branch_id,
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

        return redirect('/dashboard');
    }
}
