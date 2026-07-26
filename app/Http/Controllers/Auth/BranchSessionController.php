<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class BranchSessionController extends Controller
{
    public function form()
    {
        if (! session('logged_in')) {
            return redirect('/login');
        }

        $user = User::find(session('user_id'));

        if (! $user) {
            return redirect('/login');
        }

        return view('templates.auth.change_branch', [
            'userName' => session('user_name'),
            'currentBranchId' => session('active_branch_id'),
            'branches' => $user->branches()->orderBy('Branch_Name')->get(),
        ]);
    }

    public function update(Request $request)
    {
        if (! session('logged_in')) {
            return redirect('/login');
        }

        $request->validate(['branch_id' => 'required|integer']);

        $user = User::find(session('user_id'));
        $branch = $user?->branches()->where('tbl_branches.Branch_ID', $request->branch_id)->first();

        if (! $branch) {
            return back()->with('error', 'You are not assigned to that Branch.');
        }

        // Sub Department is branch-scoped — wipe it so nothing from the old branch leaks into the new one.
        $request->session()->forget(['active_subdepartment_id', 'active_subdepartment_module']);

        $request->session()->put([
            'db_id'              => $branch->Branch_ID,
            'active_branch_id'   => $branch->Branch_ID,
            'active_branch_name' => $branch->Branch_Name,
        ]);

        return redirect('/dashboard')->with('success', 'Switched to ' . $branch->Branch_Name . '.');
    }
}