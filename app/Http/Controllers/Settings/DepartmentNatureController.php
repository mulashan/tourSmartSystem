<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\DepartmentNature;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentNatureController extends Controller
{
    public function index(): View|RedirectResponse
    {
        return $this->nicePage('templates.settings.department-natures', 'settings.department-natures', [
            'departmentNatures' => DepartmentNature::query()->latest('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'department_nature' => ['required', 'string', 'max:255'],
        ]);

        DepartmentNature::create($validated);

        return redirect()->route('settings.department-natures')->with('success', 'Department nature saved successfully.');
    }
}
