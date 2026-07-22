<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $branches = Branch::query()
            ->latest('Branch_ID')
            ->get()
            ->map(fn (Branch $branch): array => $this->branchRow($branch));

        return $this->nicePage('templates.settings.branch', 'settings.branch', [
            'branches' => $branches,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_name' => ['required', 'string', 'max:300', 'unique:tbl_branches,Branch_Name'],
            'location' => ['nullable', 'string', 'max:255'],
            'manager' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:11'],
            'token' => ['nullable', 'string', 'max:255'],
            'token_date' => ['nullable', 'date'],
            'banner_link' => ['nullable', 'url', 'max:200'],
        ]);

        Branch::create([
            'Branch_Name' => $validated['branch_name'],
            'Location' => $validated['location'] ?? null,
            'Manager' => $validated['manager'] ?? null,
            'status' => $validated['status'] ?? null,
            'token' => $validated['token'] ?? null,
            'token_date' => $validated['token_date'] ?? null,
            'BannerLink' => $validated['banner_link'] ?? null,
        ]);

        return redirect()
            ->route('settings.branch')
            ->with('success', 'Branch saved successfully.');
    }

    private function branchRow(Branch $branch): array
    {
        $tones = ['teal', 'tan', 'slate', 'indigo'];

        return [
            'name' => $branch->Branch_Name,
            'code' => 'BR-' . str_pad((string) $branch->Branch_ID, 3, '0', STR_PAD_LEFT),
            'manager' => $branch->Manager ?: 'Not assigned',
            'status' => $branch->status ?: 'Pending',
            'location' => $branch->Location ?: 'Not set',
            'created' => optional($branch->token_date)->format('M j, Y') ?: 'Not set',
            'tone' => $tones[$branch->Branch_ID % count($tones)],
        ];
    }
}
