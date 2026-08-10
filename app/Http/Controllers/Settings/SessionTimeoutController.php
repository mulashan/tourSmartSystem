<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SessionTimeoutController extends Controller
{
    public function list(): View
    {
        return view('templates.settings.other_settings.partials.session_timeout_table', [
            'branches' => Branch::orderBy('Branch_Name')->get(),
        ]);
    }

    public function update(Request $request, Branch $branch): JsonResponse
    {
        $data = $request->validate([
            'session_timeout_minutes' => 'required|integer|min:5|max:480',
        ]);

        $branch->update($data);

        return response()->json(['success' => true]);
    }
}