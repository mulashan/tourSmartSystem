<?php

namespace App\Services;

use App\Models\MenuModule;
use Illuminate\Support\Facades\Schema;
use Throwable;

class MenuService
{
    public function page(string $view, string $active, array $data = [])
    {
        if (! session('logged_in') && ! auth()->check()) {
            return redirect('/login');
        }

        if (! $this->canAccess($active)) {
            return redirect('/dashboard');
        }

        return view($view, array_merge([
            'institutionName' => session('institution_name', 'NiceAdmin'),
            'menus' => $this->menus($active),
            'activePage' => $active,
        ], $data));
    }

    public function menus(string $active = 'dashboard'): array
    {
        return $this->filterMenus($this->catalog($active));
    }

    public function permissionMenus(): array
    {
        $menus = $this->catalog('__permission_catalog__');

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

    public function canAccess(string $active): bool
    {
        if ($active === 'dashboard' || session('permission_bypass', false)) {
            return true;
        }

        return collect(session('allowed_menu_keys', []))->contains($active);
    }

    public function catalog(string $active = 'dashboard'): array
    {
        try {
            if (! Schema::hasTable('tbl_menus')) {
                return $this->fallbackCatalog($active);
            }

            $this->seedMenuTable();

            $modules = MenuModule::query()
                ->where('is_menu', true)
                ->orderBy('parent_id')
                ->orderBy('module_id')
                ->get();

            if ($modules->isEmpty()) {
                return $this->fallbackCatalog($active);
            }

            return $this->buildMenusFromModules($modules, $active);
        } catch (Throwable) {
            return $this->fallbackCatalog($active);
        }
    }

    private function fallbackCatalog(string $active = 'dashboard'): array
    {
        $is = fn (string $key): bool => $active === $key;

        return [
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
                            ['key' => 'settings.employee-job-codes', 'label' => 'Employee Job Codes', 'url' => 'settings/employee-job-codes', 'active' => $is('settings.employee-job-codes')],
                            ['key' => 'settings.hr-employment-types', 'label' => 'HR Employment Type', 'url' => 'settings/hr-employment-types', 'active' => $is('settings.hr-employment-types')],
                            ['key' => 'settings.job-titles', 'label' => 'Job Titles', 'url' => 'settings/job-titles', 'active' => $is('settings.job-titles')],
                            ['key' => 'settings.department-natures', 'label' => 'Department Nature', 'url' => 'settings/department-natures', 'active' => $is('settings.department-natures')],
                            ['key' => 'settings.designations', 'label' => 'Designation', 'url' => 'settings/designations', 'active' => $is('settings.designations')],
                            ['key' => 'settings.employee-units', 'label' => 'Employee Units', 'url' => 'settings/employee-units', 'active' => $is('settings.employee-units')],
                        ],
                    ],
                    ['key' => 'departments.list', 'label' => 'Department', 'icon' => 'bi-diagram-3', 'url' => 'departments', 'active' => $is('departments.list')],
                    ['key' => 'employees.list', 'label' => 'Employee', 'icon' => 'bi-person-badge', 'url' => 'employees', 'active' => $is('employees.list')],
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
    }

    private function seedMenuTable(): void
    {
        if (MenuModule::query()->exists()) {
            return;
        }

        $insertedIds = [];
        $nextId = 1;
        $hasParentId2 = Schema::hasColumn('tbl_menus', 'parent_id2');

        foreach ($this->flattenFallbackMenus($this->fallbackCatalog('__seed__')) as $row) {
            $parentId = $row['parent_key'] ? ($insertedIds[$row['parent_key']] ?? null) : null;

            $data = [
                'module_id' => $nextId++,
                'name' => $row['key'],
                'label' => $row['label'],
                'menu_icon' => $this->menuIcon($row['key']),
                'route_path' => $row['url'] === '#' ? null : $row['url'],
                'parent_id' => $parentId,
                'is_menu' => true,
                'description' => $row['label'],
                'is_dashboard' => $row['key'] === 'dashboard' ? 1 : 0,
                'collapse' => $row['has_children'] ? 1 : 0,
                'new_message' => (int) ($row['new_message'] ?? 0),
            ];

            if ($hasParentId2) {
                $data['parent_id2'] = $row['parent2_key'] ? ($insertedIds[$row['parent2_key']] ?? null) : null;
            }

            $module = MenuModule::create($data);
            $insertedIds[$row['key']] = $module->module_id;
        }
    }

    private function flattenFallbackMenus(array $groups): array
    {
        $rows = [];

        foreach ($groups as $group) {
            foreach ($group['items'] as $item) {
                $rows[] = [
                    'key' => $item['key'],
                    'label' => $item['label'],
                    'url' => $item['url'] ?? '#',
                    'parent_key' => null,
                    'parent2_key' => null,
                    'has_children' => !empty($item['children']),
                ];

                foreach ($item['children'] ?? [] as $child) {
                    $rows[] = [
                        'key' => $child['key'],
                        'label' => $child['label'],
                        'url' => $child['url'] ?? '#',
                        'parent_key' => $item['key'],
                        'parent2_key' => null,
                        'has_children' => !empty($child['children']),
                    ];

                    foreach ($child['children'] ?? [] as $nested) {
                        $rows[] = [
                            'key' => $nested['key'],
                            'label' => $nested['label'],
                            'url' => $nested['url'] ?? '#',
                            'parent_key' => $item['key'],
                            'parent2_key' => $child['key'],
                            'has_children' => false,
                        ];
                    }
                }
            }
        }

        return $rows;
    }

    private function buildMenusFromModules($modules, string $active): array
    {
        $childrenByParent = $modules->groupBy(fn (MenuModule $module) => $this->effectiveParentId($module));

        $items = collect($childrenByParent->get(0, []))
            ->map(fn (MenuModule $module): array => $this->moduleMenuItem($module, $childrenByParent, $active))
            ->values()
            ->all();

        return [
            ['title' => '', 'items' => $items],
        ];
    }

    private function effectiveParentId(MenuModule $module): int
    {
        return (int) ($module->parent_id2 ?: $module->parent_id ?: 0);
    }

    private function moduleMenuItem(MenuModule $module, $childrenByParent, string $active): array
    {
        $children = collect($childrenByParent->get($module->module_id, []))
            ->map(fn (MenuModule $child): array => $this->moduleMenuItem($child, $childrenByParent, $active))
            ->values()
            ->all();

        $key = $module->name;
        $item = [
            'key' => $key,
            'label' => $module->label ?: $module->name,
            'icon' => $module->menu_icon ?: $this->menuIcon($key),
            'url' => $module->route_path ?: '#',
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

    private function menuIcon(string $key): string
    {
        return [
            'dashboard' => 'bi-grid',
            'dashboards' => 'bi-speedometer2',
            'users.setup' => 'bi-people',
            'settings.setup' => 'bi-gear',
            'departments.list' => 'bi-diagram-3',
            'employees.list' => 'bi-person-badge',
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
        ][$key] ?? 'bi-circle';
    }

    private function filterMenus(array $menus): array
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
}
