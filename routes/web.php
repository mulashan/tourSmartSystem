<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use App\Models\MenuModule;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Settings\BranchController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Users\RolesPermissionController;
use App\Http\Controllers\Users\UserTypeController;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', function () {
    return view('templates.login');
})->name('login');

Route::get('/register', function () {
    return view('templates.register');
})->name('register');

Route::post('/validate', [LoginController::class, 'validateLogin']);

function niceFallbackMenuCatalog(string $active = 'dashboard'): array
{
    $is = fn (string $key): bool => $active === $key;

    $menus = [
        [
            'title' => '',
            'items' => [
                ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'bi-grid', 'url' => 'dashboard', 'active' => $is('dashboard'), 'badge' => 'Home'],
                ['key' => 'dashboards', 'label' => 'Dashboards', 'icon' => 'bi-speedometer2', 'url' => '#', 'badge' => '6'],
                [
                    'key' => 'users.setup',
                    'label' => 'Users setup',
                    'icon' => 'bi-people',
                    'url' => '#',
                    'open' => str_starts_with($active, 'users.'),
                    'children' => [
                        ['key' => 'users.list', 'label' => 'Users List', 'url' => 'users', 'active' => $is('users.list')],
                        ['key' => 'users.types', 'label' => 'User Types', 'url' => 'users/types', 'active' => $is('users.types')],
                        ['key' => 'users.view', 'label' => 'User View', 'url' => 'users/view', 'active' => $is('users.view')],
                        ['key' => 'users.edit', 'label' => 'User Edit', 'url' => 'users/edit', 'active' => $is('users.edit')],
                        ['key' => 'users.profile', 'label' => 'Profile', 'url' => 'users/profile', 'active' => $is('users.profile')],
                        [
                            'key' => 'users.settings.group',
                            'label' => 'Settings',
                            'url' => '#',
                            'active' => $is('users.settings') || $is('users.notifications') || $is('users.activity'),
                            'chevron' => true,
                            'children' => [
                                ['key' => 'users.settings', 'label' => 'Account', 'url' => 'users/settings', 'active' => $is('users.settings')],
                                ['key' => 'users.notifications', 'label' => 'Notifications', 'url' => 'users/notifications', 'active' => $is('users.notifications')],
                                ['key' => 'users.activity', 'label' => 'Activity', 'url' => 'users/activity', 'active' => $is('users.activity')],
                            ],
                        ],
                        ['key' => 'users.roles', 'label' => 'Roles & Permissions', 'url' => 'users/roles-permissions', 'active' => $is('users.roles')],
                    ],
                ],
                [
                    'key' => 'settings.setup',
                    'label' => 'Setting',
                    'icon' => 'bi-gear',
                    'url' => '#',
                    'open' => str_starts_with($active, 'settings.'),
                    'children' => [
                        ['key' => 'settings.branch', 'label' => 'Branch', 'url' => 'settings/branch', 'active' => $is('settings.branch')],
                    ],
                ],
                ['key' => 'authentication', 'label' => 'Authentication', 'icon' => 'bi-shield-check', 'url' => '#', 'badge' => '7'],
            ],
        ],
        [
            'title' => 'Productivity Apps',
            'items' => [
                ['key' => 'apps.calendar', 'label' => 'Calendar', 'icon' => 'bi-calendar4-week', 'url' => 'apps/calendar', 'active' => $is('apps.calendar')],
                ['key' => 'apps.kanban', 'label' => 'Kanban Board', 'icon' => 'bi-kanban', 'url' => 'apps/kanban-board', 'active' => $is('apps.kanban')],
                ['key' => 'apps.chat', 'label' => 'Chat', 'icon' => 'bi-chat-left-dots', 'url' => 'apps/chat', 'active' => $is('apps.chat')],
                ['key' => 'apps.contacts', 'label' => 'Contacts', 'icon' => 'bi-person-lines-fill', 'url' => 'apps/contacts', 'active' => $is('apps.contacts')],
                ['key' => 'apps.files', 'label' => 'File Manager', 'icon' => 'bi-folder2-open', 'url' => 'apps/file-manager', 'active' => $is('apps.files')],
                ['key' => 'apps.email', 'label' => 'Email', 'icon' => 'bi-envelope', 'url' => 'apps/email', 'active' => $is('apps.email')],
                ['key' => 'apps.todo', 'label' => 'Todo List', 'icon' => 'bi-check2-all', 'url' => 'apps/todo-list', 'active' => $is('apps.todo')],
                ['key' => 'apps.support', 'label' => 'Support Center', 'icon' => 'bi-headset', 'url' => 'apps/support-center', 'active' => $is('apps.support')],
            ],
        ],
        [
            'title' => 'Interface',
            'items' => [
                ['key' => 'interface.components', 'label' => 'Components', 'icon' => 'bi-box', 'url' => '#', 'chevron' => true],
                ['key' => 'interface.widgets', 'label' => 'Widgets', 'icon' => 'bi-layers', 'url' => '#', 'chevron' => true],
                ['key' => 'interface.forms', 'label' => 'Forms', 'icon' => 'bi-ui-checks-grid', 'url' => '#', 'chevron' => true],
                ['key' => 'interface.tables', 'label' => 'Tables', 'icon' => 'bi-table', 'url' => '#', 'chevron' => true],
                ['key' => 'interface.charts', 'label' => 'Charts', 'icon' => 'bi-bar-chart', 'url' => '#', 'chevron' => true],
                ['key' => 'interface.icons', 'label' => 'Icons', 'icon' => 'bi-diamond', 'url' => '#', 'chevron' => true],
            ],
        ],
        [
            'title' => 'Utility Pages',
            'items' => [
                ['key' => 'utility.contact', 'label' => 'Contact', 'icon' => 'bi-send', 'url' => 'utility/contact', 'active' => $is('utility.contact')],
                [
                    'key' => 'utility.invoices',
                    'label' => 'Invoices',
                    'icon' => 'bi-receipt',
                    'url' => '#',
                    'open' => str_starts_with($active, 'utility.invoices.'),
                    'children' => [
                        ['key' => 'utility.invoices.list', 'label' => 'Invoice List', 'url' => 'utility/invoices', 'active' => $is('utility.invoices.list')],
                        ['key' => 'utility.invoices.view', 'label' => 'Invoice View', 'url' => 'utility/invoices/view', 'active' => $is('utility.invoices.view')],
                    ],
                ],
                ['key' => 'utility.pricing', 'label' => 'Pricing', 'icon' => 'bi-tag', 'url' => 'utility/pricing', 'active' => $is('utility.pricing')],
                ['key' => 'utility.faq', 'label' => 'FAQ', 'icon' => 'bi-question-circle', 'url' => 'utility/faq', 'active' => $is('utility.faq')],
                ['key' => 'utility.errors', 'label' => 'Error Pages', 'icon' => 'bi-exclamation-triangle', 'url' => 'utility/error-pages', 'active' => $is('utility.errors'), 'chevron' => true],
                ['key' => 'utility.timeline', 'label' => 'Timeline', 'icon' => 'bi-clock-history', 'url' => 'utility/timeline', 'active' => $is('utility.timeline')],
                ['key' => 'utility.search', 'label' => 'Search Results', 'icon' => 'bi-search', 'url' => 'utility/search-results', 'active' => $is('utility.search')],
                ['key' => 'utility.blank', 'label' => 'Blank Page', 'icon' => 'bi-file-earmark', 'url' => 'utility/blank-page', 'active' => $is('utility.blank')],
            ],
        ],
    ];

    return $menus;
}

function niceMenuCatalog(string $active = 'dashboard'): array
{
    try {
        if (! Schema::hasTable('tbl_menus')) {
            return niceFallbackMenuCatalog($active);
        }

        niceSeedMenuTable();

        $modules = MenuModule::query()
            ->where('is_menu', true)
            ->orderBy('parent_id')
            ->orderBy('module_id')
            ->get();

        if ($modules->isEmpty()) {
            return niceFallbackMenuCatalog($active);
        }

        return niceBuildMenusFromModules($modules, $active);
    } catch (Throwable) {
        return niceFallbackMenuCatalog($active);
    }
}

function niceSeedMenuTable(): void
{
    if (MenuModule::query()->exists()) {
        return;
    }

    $insertedIds = [];
    $nextId = 1;

    foreach (niceFlattenFallbackMenus(niceFallbackMenuCatalog('__seed__')) as $row) {
        $parentId = $row['parent_key'] ? ($insertedIds[$row['parent_key']] ?? null) : null;

        $module = MenuModule::create([
            'module_id' => $nextId++,
            'name' => $row['key'],
            'label' => $row['label'],
            'menu_icon' => niceMenuIcon($row['key']),
            'route_path' => $row['url'] === '#' ? null : $row['url'],
            'parent_id' => $parentId,
            'is_menu' => true,
            'description' => $row['label'],
            'is_dashboard' => $row['key'] === 'dashboard' ? 1 : 0,
            'collapse' => $row['has_children'] ? 1 : 0,
            'new_message' => (int) ($row['new_message'] ?? 0),
        ]);

        $insertedIds[$row['key']] = $module->module_id;
    }
}

function niceFlattenFallbackMenus(array $groups): array
{
    $rows = [];

    foreach ($groups as $group) {
        foreach ($group['items'] as $item) {
            $rows[] = [
                'key' => $item['key'],
                'label' => $item['label'],
                'url' => $item['url'] ?? '#',
                'parent_key' => null,
                'has_children' => !empty($item['children']),
            ];

            foreach ($item['children'] ?? [] as $child) {
                $rows[] = [
                    'key' => $child['key'],
                    'label' => $child['label'],
                    'url' => $child['url'] ?? '#',
                    'parent_key' => $item['key'],
                    'has_children' => !empty($child['children']),
                ];

                foreach ($child['children'] ?? [] as $nested) {
                    $rows[] = [
                        'key' => $nested['key'],
                        'label' => $nested['label'],
                        'url' => $nested['url'] ?? '#',
                        'parent_key' => $child['key'],
                        'has_children' => false,
                    ];
                }
            }
        }
    }

    return $rows;
}

function niceBuildMenusFromModules($modules, string $active): array
{
    $childrenByParent = $modules->groupBy(fn (MenuModule $module) => (int) ($module->parent_id ?? 0));

    $items = collect($childrenByParent->get(0, []))
        ->map(fn (MenuModule $module): array => niceModuleMenuItem($module, $childrenByParent, $active))
        ->values()
        ->all();

    return [
        ['title' => '', 'items' => $items],
    ];
}

function niceModuleMenuItem(MenuModule $module, $childrenByParent, string $active): array
{
    $children = collect($childrenByParent->get($module->module_id, []))
        ->map(fn (MenuModule $child): array => niceModuleMenuItem($child, $childrenByParent, $active))
        ->values()
        ->all();

    $key = $module->name;
    $url = $module->route_path ?: '#';
    $item = [
        'key' => $key,
        'label' => $module->label ?: $module->name,
        'icon' => $module->menu_icon ?: niceMenuIcon($key),
        'url' => $url,
        'active' => $active === $key,
    ];

    if (!empty($children)) {
        $item['children'] = $children;
        $item['open'] = str_starts_with($active, strtok($key, '.') . '.')
            || collect($children)->contains(fn (array $child): bool => !empty($child['active']) || !empty($child['open']));
        $item['chevron'] = true;
    }

    if ($module->is_dashboard) {
        $item['badge'] = 'Home';
    }

    if ($module->new_message) {
        $item['badge'] = (string) $module->new_message;
    }

    return $item;
}

function niceMenuIcon(string $key): string
{
    $icons = [
        'dashboard' => 'bi-grid',
        'dashboards' => 'bi-speedometer2',
        'users.setup' => 'bi-people',
        'settings.setup' => 'bi-gear',
        'authentication' => 'bi-shield-check',
        'apps.calendar' => 'bi-calendar4-week',
        'apps.kanban' => 'bi-kanban',
        'apps.chat' => 'bi-chat-left-dots',
        'apps.contacts' => 'bi-person-lines-fill',
        'apps.files' => 'bi-folder2-open',
        'apps.email' => 'bi-envelope',
        'apps.todo' => 'bi-check2-all',
        'apps.support' => 'bi-headset',
        'utility.contact' => 'bi-send',
        'utility.invoices' => 'bi-receipt',
        'utility.pricing' => 'bi-tag',
        'utility.faq' => 'bi-question-circle',
        'utility.errors' => 'bi-exclamation-triangle',
        'utility.timeline' => 'bi-clock-history',
        'utility.search' => 'bi-search',
        'utility.blank' => 'bi-file-earmark',
    ];

    return $icons[$key] ?? 'bi-circle';
}

function niceMenus(string $active = 'dashboard'): array
{
    return niceFilterMenus(niceMenuCatalog($active));
}

function nicePermissionMenus(): array
{
    $menus = niceMenuCatalog('__permission_catalog__');

    return collect($menus)->map(function (array $group): array {
        $items = collect($group['items'])->flatMap(function (array $item): array {
            $rows = [['key' => $item['key'], 'label' => $item['label'], 'url' => $item['url'] ?? '#']];

            foreach ($item['children'] ?? [] as $child) {
                $rows[] = ['key' => $child['key'], 'label' => $child['label'], 'url' => $child['url'] ?? '#'];

                foreach ($child['children'] ?? [] as $nested) {
                    $rows[] = ['key' => $nested['key'], 'label' => $nested['label'], 'url' => $nested['url'] ?? '#'];
                }
            }

            return $rows;
        })->unique('key')->values()->all();

        return [
            'title' => $group['title'] ?: 'Main',
            'items' => collect($items)->filter(fn (array $item): bool => ($item['url'] ?? '#') !== '#')->values()->all(),
        ];
    })->filter(fn (array $group): bool => !empty($group['items']))->values()->all();
}

function niceFilterMenus(array $menus): array
{
    $allowed = collect(session('allowed_menu_keys', []))->push('dashboard')->unique();

    if (session('permission_bypass', false)) {
        return $menus;
    }

    return collect($menus)->map(function (array $group) use ($allowed): array {
        $group['items'] = collect($group['items'])->map(function (array $item) use ($allowed): ?array {
            if (!empty($item['children'])) {
                $item['children'] = collect($item['children'])->map(function (array $child) use ($allowed): ?array {
                    if (!empty($child['children'])) {
                        $child['children'] = collect($child['children'])
                            ->filter(fn (array $nested): bool => $allowed->contains($nested['key']))
                            ->values()
                            ->all();

                        return !empty($child['children']) ? $child : null;
                    }

                    return $allowed->contains($child['key']) ? $child : null;
                })->filter()->values()->all();

                return !empty($item['children']) ? $item : null;
            }

            return $allowed->contains($item['key']) ? $item : null;
        })->filter()->values()->all();

        return $group;
    })->filter(fn (array $group): bool => !empty($group['items']))->values()->all();
}

function nicePage(string $view, string $active, array $data = [])
{
    if (! session('logged_in') && ! auth()->check()) {
        return redirect('/login');
    }

    if (! niceCanAccess($active)) {
        return redirect('/dashboard');
    }

    return view($view, array_merge([
        'institutionName' => session('institution_name', 'NiceAdmin'),
        'menus' => niceMenus($active),
        'activePage' => $active,
    ], $data));
}

function niceCanAccess(string $active): bool
{
    if ($active === 'dashboard' || session('permission_bypass', false)) {
        return true;
    }

    return collect(session('allowed_menu_keys', []))->contains($active);
}


Route::get('/dashboard', function () {
    $calendarMonths = collect(range(1, 12))->map(function ($month) {
        return [
            'month_number' => $month,
            'month_name' => date('F', mktime(0, 0, 0, $month, 1)),
            'events' => $month === (int) date('n')
                ? [(object) ['id' => 1, 'event_date' => date('Y-m-d'), 'event_description' => 'Demo school calendar event']]
                : [],
        ];
    })->all();

    return nicePage('templates.dashboard', 'dashboard', [
        'releaseHistory' => [
            [
                'version' => '1.0.0',
                'released_at' => now()->format('Y-m-d H:i'),
                'title' => 'Laravel template ready',
                'changes' => ['CI4 template converted to Laravel Blade.', 'Demo routes added for login and dashboard.'],
            ],
        ],
        'copyrightName' => 'MHI',
        'copyrightUrl' => '#',
        'dashboardCalendarMonths' => $calendarMonths,
        'dashboardCurrentMonth' => $calendarMonths[(int) date('n') - 1],
        'dashboardCalendarYear' => (int) date('Y'),
    ]);
});

Route::get('/users', [UserController::class, 'index'])->name('users.list');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::get('/users/types', [UserTypeController::class, 'index'])->name('users.types');
Route::post('/users/types', [UserTypeController::class, 'store'])->name('users.types.store');
Route::get('/users/view', fn () => nicePage('templates.users.page', 'users.view', ['page' => 'view']));
Route::get('/users/edit', fn () => nicePage('templates.users.page', 'users.edit', ['page' => 'edit']));
Route::get('/users/profile', fn () => nicePage('templates.users.page', 'users.profile', ['page' => 'profile']));
Route::get('/users/settings', fn () => nicePage('templates.users.page', 'users.settings', ['page' => 'settings']));
Route::get('/users/notifications', fn () => nicePage('templates.users.page', 'users.notifications', ['page' => 'notifications']));
Route::get('/users/activity', fn () => nicePage('templates.users.page', 'users.activity', ['page' => 'activity']));
Route::get('/users/roles-permissions', [RolesPermissionController::class, 'index'])->name('users.roles');
Route::post('/users/roles-permissions', [RolesPermissionController::class, 'store'])->name('users.roles.store');
Route::get('/settings/branch', [BranchController::class, 'index'])->name('settings.branch');
Route::post('/settings/branch', [BranchController::class, 'store'])->name('settings.branch.store');

Route::get('/apps/calendar', fn () => nicePage('templates.apps.page', 'apps.calendar', ['page' => 'calendar']));
Route::get('/apps/chat', fn () => nicePage('templates.apps.page', 'apps.chat', ['page' => 'chat']));
Route::get('/apps/file-manager', fn () => nicePage('templates.apps.page', 'apps.files', ['page' => 'file-manager']));
Route::get('/apps/kanban-board', fn () => nicePage('templates.apps.page', 'apps.kanban', ['page' => 'placeholder', 'title' => 'Kanban Board', 'crumb' => 'Kanban Board']));
Route::get('/apps/contacts', fn () => nicePage('templates.apps.page', 'apps.contacts', ['page' => 'placeholder', 'title' => 'Contacts', 'crumb' => 'Contacts']));
Route::get('/apps/email', fn () => nicePage('templates.apps.page', 'apps.email', ['page' => 'placeholder', 'title' => 'Email', 'crumb' => 'Email']));
Route::get('/apps/todo-list', fn () => nicePage('templates.apps.page', 'apps.todo', ['page' => 'placeholder', 'title' => 'Todo List', 'crumb' => 'Todo List']));
Route::get('/apps/support-center', fn () => nicePage('templates.apps.page', 'apps.support', ['page' => 'placeholder', 'title' => 'Support Center', 'crumb' => 'Support Center']));

Route::get('/utility/invoices', fn () => nicePage('templates.utility.page', 'utility.invoices.list', ['page' => 'invoice-list']));
Route::get('/utility/invoices/view', fn () => nicePage('templates.utility.page', 'utility.invoices.view', ['page' => 'invoice-view']));
Route::get('/utility/pricing', fn () => nicePage('templates.utility.page', 'utility.pricing', ['page' => 'pricing']));
Route::get('/utility/contact', fn () => nicePage('templates.utility.page', 'utility.contact', ['page' => 'placeholder', 'title' => 'Contact']));
Route::get('/utility/faq', fn () => nicePage('templates.utility.page', 'utility.faq', ['page' => 'placeholder', 'title' => 'FAQ']));
Route::get('/utility/error-pages', fn () => nicePage('templates.utility.page', 'utility.errors', ['page' => 'placeholder', 'title' => 'Error Pages']));
Route::get('/utility/timeline', fn () => nicePage('templates.utility.page', 'utility.timeline', ['page' => 'placeholder', 'title' => 'Timeline']));
Route::get('/utility/search-results', fn () => nicePage('templates.utility.page', 'utility.search', ['page' => 'placeholder', 'title' => 'Search Results']));
Route::get('/utility/blank-page', fn () => nicePage('templates.utility.page', 'utility.blank', ['page' => 'placeholder', 'title' => 'Blank Page']));

Route::get('/Logout', function (Request $request) {
    $request->session()->flush();

    return redirect('/login');
});

Route::get('/Logout2', function () {
    return redirect('/login');
});

Route::get('/load_privacy', function () {
    return '<p class="mb-0">Privacy policy content goes here.</p>';
});

Route::get('/profile_edit/{user}', function ($user) {
    return '<div class="p-3"><h5>Profile</h5><p>User #' . e($user) . '</p></div>';
});

Route::post('/system/releases/seen', function () {
    return response()->json(['ok' => true]);
});

Route::post('/system/releases/new', function () {
    return response()->json([['seen' => 1]]);
});

Route::post('/view_school_calendar', function (Request $request) {
    return '<div><strong>Demo event</strong><p class="mb-0">Calendar event #' . e($request->input('id')) . '</p></div>';
});

Route::post('/dashboard_school_calendar_widget', function () {
    $calendarMonths = collect(range(1, 12))->map(function ($month) {
        return [
            'month_number' => $month,
            'month_name' => date('F', mktime(0, 0, 0, $month, 1)),
            'events' => $month === (int) date('n')
                ? [(object) ['id' => 1, 'event_date' => date('Y-m-d'), 'event_description' => 'Demo school calendar event']]
                : [],
        ];
    })->all();

    return view('templates.dashboard_school_calendar_widget', [
        'dashboardCalendarMonths' => $calendarMonths,
        'dashboardCurrentMonth' => $calendarMonths[(int) date('n') - 1],
        'dashboardCalendarYear' => (int) date('Y'),
    ]);
});

Route::get('/company/load-content-view/{year}', function ($year) {
    return view('templates.dashboard_cards', ['year' => $year]);
});
