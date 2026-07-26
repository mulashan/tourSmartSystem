<?php

namespace App\Http\Controllers\StorageSupplies;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MenuService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class SubdepartmentSessionController extends Controller
{
    public function index(string $module): View|RedirectResponse
    {
        if (! session('logged_in')) {
            return redirect('/login');
        }

        $config = $this->moduleConfig($module);

        return view('templates.storage_supplies.select_subdepartment', [
            'institutionName' => session('institution_name', 'NiceAdmin'),
            'menus' => app(MenuService::class)->menus($config['active']),
            'activePage' => $config['active'],
            'module' => $module,
            'moduleLabel' => $config['nature'],
            'subdepartments' => $this->assignedSubdepartments($config['nature']),
        ]);
    }

    public function store(Request $request, string $module): RedirectResponse
    {
        $config = $this->moduleConfig($module);

        $request->validate(['subdepartment_id' => 'required|integer']);

        $allowedIds = $this->assignedSubdepartments($config['nature'])->pluck('Subdepartment_ID')->all();

        if (! in_array((int) $request->subdepartment_id, $allowedIds, true)) {
            return back()->with('error', 'You are not assigned to that Sub Department.');
        }

        $request->session()->put([
            'active_subdepartment_id' => (int) $request->subdepartment_id,
            'active_subdepartment_module' => $module,
        ]);

        $intended = $request->session()->pull('url.intended');

        return redirect($intended ?: route($config['default_route']));
    }

    private function moduleConfig(string $module): array
    {
        $config = config("storage_modules.{$module}");

        abort_unless($config, Response::HTTP_NOT_FOUND, 'Unknown module context.');

        return $config;
    }

    private function assignedSubdepartments(string $natureLabel)
    {
        $user = User::with('subdepartments.department.departmentNature', 'subdepartments.department.branch')->find(session('user_id'));
        $activeBranchId = session('active_branch_id');

        return ($user?->subdepartments ?? collect())
            ->filter(fn ($sub) => optional($sub->department?->departmentNature)->department_nature === $natureLabel)
            ->filter(fn ($sub) => ! $activeBranchId || $sub->department?->Branch_ID == $activeBranchId)
            ->values();
    }
}