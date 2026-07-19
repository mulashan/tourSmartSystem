<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class SystemDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCompany();
        $branchId = $this->seedBranch();
        $roles = $this->seedUserTypes();
        $menus = $this->seedMenus();
        $this->seedAdminPermissions($roles['admin'], $menus);
        $this->seedAdminUser($branchId, $roles['admin']);
    }

    private function seedCompany(): void
    {
        DB::table('tbl_company')->updateOrInsert(
            ['Company_Name' => 'Leopard Tours'],
            ['status' => 'active']
        );
    }

    private function seedBranch(): int
    {
        $companyId = DB::table('tbl_company')->where('Company_Name', 'Leopard Tours')->value('Company_ID');

        $data = [
            'Branch_Name' => 'Head Office',
            'token' => 'HQ-DEFAULT',
            'token_date' => now(),
            'BannerLink' => 'https://example.com/banner.jpg',
            'Company_ID' => $companyId,
        ];

        if (Schema::hasColumn('tbl_branches', 'Location')) {
            $data['Location'] = 'Dar es Salaam';
        }

        if (Schema::hasColumn('tbl_branches', 'Manager')) {
            $data['Manager'] = 'System Admin';
        }

        if (Schema::hasColumn('tbl_branches', 'status')) {
            $data['status'] = 'Active';
        }

        DB::table('tbl_branches')->updateOrInsert(
            ['Branch_Name' => 'Head Office'],
            $data
        );

        return (int) DB::table('tbl_branches')->where('Branch_Name', 'Head Office')->value('Branch_ID');
    }

    private function seedUserTypes(): array
    {
        $types = [
            'admin' => [
                'privilege_name' => 'Admin',
                'privilege_desc' => 'Full system access and permission assignment.',
                'access_level_id' => 9,
                'priv_status' => true,
            ],
            'manager' => [
                'privilege_name' => 'Manager',
                'privilege_desc' => 'Operational access for branch and team management.',
                'access_level_id' => 5,
                'priv_status' => true,
            ],
            'user' => [
                'privilege_name' => 'User',
                'privilege_desc' => 'Standard user access.',
                'access_level_id' => 1,
                'priv_status' => true,
            ],
        ];

        $ids = [];

        foreach ($types as $key => $type) {
            DB::table('tbl_users_privileges')->updateOrInsert(
                ['privilege_name' => $type['privilege_name']],
                $type
            );

            $ids[$key] = (int) DB::table('tbl_users_privileges')
                ->where('privilege_name', $type['privilege_name'])
                ->value('id');
        }

        return $ids;
    }

    private function seedMenus(): array
    {
        $menus = [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'bi-grid', 'url' => 'dashboard', 'parent' => null, 'dashboard' => 1],
            ['key' => 'dashboards', 'label' => 'Dashboards', 'icon' => 'bi-speedometer2', 'url' => null, 'parent' => null],
            ['key' => 'users.setup', 'label' => 'Users setup', 'icon' => 'bi-people', 'url' => null, 'parent' => null, 'collapse' => 1],
            ['key' => 'users.list', 'label' => 'Users List', 'icon' => null, 'url' => 'users', 'parent' => 'users.setup'],
            ['key' => 'users.types', 'label' => 'User Types', 'icon' => null, 'url' => 'users/types', 'parent' => 'users.setup'],
            ['key' => 'users.view', 'label' => 'User View', 'icon' => null, 'url' => 'users/view', 'parent' => 'users.setup'],
            ['key' => 'users.edit', 'label' => 'User Edit', 'icon' => null, 'url' => 'users/edit', 'parent' => 'users.setup'],
            ['key' => 'users.profile', 'label' => 'Profile', 'icon' => null, 'url' => 'users/profile', 'parent' => 'users.setup'],
            ['key' => 'users.settings.group', 'label' => 'Settings', 'icon' => null, 'url' => null, 'parent' => 'users.setup', 'collapse' => 1],
            ['key' => 'users.settings', 'label' => 'Account', 'icon' => null, 'url' => 'users/settings', 'parent' => 'users.settings.group'],
            ['key' => 'users.notifications', 'label' => 'Notifications', 'icon' => null, 'url' => 'users/notifications', 'parent' => 'users.settings.group'],
            ['key' => 'users.activity', 'label' => 'Activity', 'icon' => null, 'url' => 'users/activity', 'parent' => 'users.settings.group'],
            ['key' => 'users.roles', 'label' => 'Roles & Permissions', 'icon' => null, 'url' => 'users/roles-permissions', 'parent' => 'users.setup'],
            ['key' => 'settings.setup', 'label' => 'Setting', 'icon' => 'bi-gear', 'url' => null, 'parent' => null, 'collapse' => 1],
            ['key' => 'settings.branch', 'label' => 'Branch', 'icon' => null, 'url' => 'settings/branch', 'parent' => 'settings.setup'],
            ['key' => 'authentication', 'label' => 'Authentication', 'icon' => 'bi-shield-check', 'url' => null, 'parent' => null, 'new_message' => 7],
            ['key' => 'apps.calendar', 'label' => 'Calendar', 'icon' => 'bi-calendar4-week', 'url' => 'apps/calendar', 'parent' => null],
            ['key' => 'apps.kanban', 'label' => 'Kanban Board', 'icon' => 'bi-kanban', 'url' => 'apps/kanban-board', 'parent' => null],
            ['key' => 'apps.chat', 'label' => 'Chat', 'icon' => 'bi-chat-left-dots', 'url' => 'apps/chat', 'parent' => null],
            ['key' => 'apps.contacts', 'label' => 'Contacts', 'icon' => 'bi-person-lines-fill', 'url' => 'apps/contacts', 'parent' => null],
            ['key' => 'apps.files', 'label' => 'File Manager', 'icon' => 'bi-folder2-open', 'url' => 'apps/file-manager', 'parent' => null],
            ['key' => 'apps.email', 'label' => 'Email', 'icon' => 'bi-envelope', 'url' => 'apps/email', 'parent' => null],
            ['key' => 'apps.todo', 'label' => 'Todo List', 'icon' => 'bi-check2-all', 'url' => 'apps/todo-list', 'parent' => null],
            ['key' => 'apps.support', 'label' => 'Support Center', 'icon' => 'bi-headset', 'url' => 'apps/support-center', 'parent' => null],
        ];

        $ids = [];
        $nextModuleId = ((int) DB::table('tbl_menus')->max('module_id')) + 1;

        foreach ($menus as $menu) {
            $parentId = $menu['parent'] ? ($ids[$menu['parent']] ?? null) : null;

            $data = [
                'name' => $menu['key'],
                'label' => $menu['label'],
                'menu_icon' => $menu['icon'],
                'route_path' => $menu['url'],
                'parent_id' => $parentId,
                'is_menu' => true,
                'description' => $menu['label'],
                'is_dashboard' => $menu['dashboard'] ?? 0,
                'collapse' => $menu['collapse'] ?? 0,
                'new_message' => $menu['new_message'] ?? 0,
            ];

            $existingId = DB::table('tbl_menus')->where('name', $menu['key'])->value('module_id');

            if ($existingId) {
                DB::table('tbl_menus')->where('module_id', $existingId)->update($data);
            } else {
                DB::table('tbl_menus')->insert(array_merge(['module_id' => $nextModuleId++], $data));
            }

            $ids[$menu['key']] = (int) DB::table('tbl_menus')->where('name', $menu['key'])->value('module_id');
        }

        return $ids;
    }

    private function seedAdminPermissions(int $adminPrivilegeId, array $menus): void
    {
        foreach (array_keys($menus) as $menuKey) {
            DB::table('user_type_menu_permissions')->updateOrInsert(
                ['privilege_id' => $adminPrivilegeId, 'menu_key' => $menuKey],
                ['can_access' => true, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    private function seedAdminUser(int $branchId, int $adminPrivilegeId): void
    {
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@example.com'],
            [
                'first_name' => 'System',
                'second_name' => 'Admin',
                'other_names' => 'User',
                'date_of_birth' => '1990-01-01',
                'gender' => 'Male',
                'branch_id' => $branchId,
                'national_id' => null,
                'privilege_id' => $adminPrivilegeId,
                'physical_address' => 'Head Office',
                'photo' => null,
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
