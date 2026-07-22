<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\JobTitle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobTitleController extends Controller
{
    public function index(): View|RedirectResponse
    {
        return $this->nicePage('templates.settings.job-titles', 'settings.job-titles', [
            'jobTitles' => JobTitle::query()->latest('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'title_location' => ['required', 'string', 'max:255'],
        ]);

        JobTitle::create($validated);

        return redirect()->route('settings.job-titles')->with('success', 'Job title saved successfully.');
    }
}
