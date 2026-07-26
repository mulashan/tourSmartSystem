<?php


// to be deleted 

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Subdepartment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserSubdepartmentController extends Controller
{
    /*public function edit(User $user): View
    {
        return $this->nicePage('templates.users.assign_subdepartments', 'users.list', [
            'targetUser' => $user,
            'subdepartments' => Subdepartment::orderBy('Subdepartment_Name')->get(),
            'assignedIds' => $user->subdepartments->pluck('Subdepartment_ID')->all(),
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $ids = $request->validate(['subdepartment_ids' => 'array'])['subdepartment_ids'] ?? [];

        $user->subdepartments()->sync($ids);

        return response()->json(['success' => true]);
    }*/
}