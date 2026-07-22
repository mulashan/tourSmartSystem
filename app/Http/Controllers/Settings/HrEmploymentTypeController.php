<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\HrEmploymentType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HrEmploymentTypeController extends Controller
{
    public function index(): View|RedirectResponse
    {
        return $this->nicePage('templates.settings.hr-employment-types', 'settings.hr-employment-types', [
            'employmentTypes' => HrEmploymentType::query()->latest('id')->get(),
            'branches' => Branch::query()->orderBy('Branch_Name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'branch_id' => ['nullable', 'integer', 'exists:tbl_branches,Branch_ID'],
        ]);

        HrEmploymentType::create($validated);

        return redirect()->route('settings.hr-employment-types')->with('success', 'HR employment type saved successfully.');
    }
}
