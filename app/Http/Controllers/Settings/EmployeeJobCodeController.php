<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\EmployeeJobCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeJobCodeController extends Controller
{
    public function index(): View|RedirectResponse
    {
        return $this->nicePage('templates.settings.employee-job-codes', 'settings.employee-job-codes', [
            'jobCodes' => EmployeeJobCode::query()->latest('employee_job_code_id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'job_code' => ['required', 'string', 'max:255', 'unique:employee_job_codes,job_code'],
        ]);

        EmployeeJobCode::create($validated);

        return redirect()->route('settings.employee-job-codes')->with('success', 'Employee job code saved successfully.');
    }
}
