<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DesignationController extends Controller
{
    public function index(): View|RedirectResponse
    {
        return $this->nicePage('templates.settings.designations', 'settings.designations', [
            'designations' => Designation::query()->latest('designation_ID')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'designation' => ['required', 'string', 'max:30'],
            'session_time_limit' => ['required', 'integer', 'min:1'],
        ]);

        Designation::create($validated);

        return redirect()->route('settings.designations')->with('success', 'Designation saved successfully.');
    }
}
