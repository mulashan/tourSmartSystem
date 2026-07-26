<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Lookup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Branch;
use App\Models\DepartmentNature;
use App\Models\Department;

class Other_SettingsController extends Controller
{
    public function index(): View|RedirectResponse
    {
        return $this->nicePage('templates.settings.other_settings.other_settings_home', 'settings.other_settings', [
            'lookupItems' => config('other_settings.lookups', []),
            'customItems' => config('other_settings.custom', []),
            'branches' => Branch::orderBy('Branch_Name')->get(),
            'departmentNatures' => DepartmentNature::orderBy('department_nature')->get(),
            'departments' => Department::with('departmentNature')->orderBy('Department_Name')->get(),
        ]);
    }

    public function list(Request $request, string $key): View
    {
        $config = $this->lookupConfig($key);

        $items = Lookup::query()
            ->ofType($config['type'])
            ->search($request->query('search'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('templates.settings.other_settings.partials.lookup_table', [
            'key' => $key,
            'config' => $config,
            'items' => $items,
        ]);
    }

    public function store(Request $request, string $key): JsonResponse
    {
        $config = $this->lookupConfig($key);

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:30',
            'description' => 'nullable|string|max:255',
        ]);

        $data['type'] = $config['type'];
        $data['is_active'] = true;

        $item = Lookup::create($data);

        return response()->json(['success' => true, 'item' => $item]);
    }

    public function update(Request $request, string $key, Lookup $lookup): JsonResponse
    {
        $config = $this->lookupConfig($key);
        abort_unless($lookup->type === $config['type'], Response::HTTP_NOT_FOUND);

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:30',
            'description' => 'nullable|string|max:255',
        ]);

        $lookup->update($data);

        return response()->json(['success' => true, 'item' => $lookup]);
    }

    public function destroy(string $key, Lookup $lookup): JsonResponse
    {
        $config = $this->lookupConfig($key);
        abort_unless($lookup->type === $config['type'], Response::HTTP_NOT_FOUND);

        $lookup->delete();

        return response()->json(['success' => true]);
    }

    private function lookupConfig(string $key): array
    {
        $config = config("other_settings.lookups.{$key}");

        abort_unless($config, Response::HTTP_NOT_FOUND, 'Unknown settings category.');

        return $config;
    }
}