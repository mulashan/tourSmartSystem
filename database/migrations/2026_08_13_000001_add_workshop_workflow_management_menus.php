<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tbl_menus')) {
            return;
        }

        $menus = [
            ['key' => 'workshop.workflow-management', 'label' => 'Workflow Management', 'url' => null, 'parent' => 'workshop.setup', 'parent2' => null, 'collapse' => 1],
            ['key' => 'workshop.workflow.job-card', 'label' => 'Job Card', 'url' => 'workshop/workflow-management/job-card', 'parent' => 'workshop.setup', 'parent2' => 'workshop.workflow-management'],
            ['key' => 'workshop.workflow.vehicle-inspection', 'label' => 'Vehicle Inspection', 'url' => 'workshop/workflow-management/vehicle-inspection', 'parent' => 'workshop.setup', 'parent2' => 'workshop.workflow-management'],
            ['key' => 'workshop.workflow.diagnosis', 'label' => 'Diagnosis', 'url' => 'workshop/workflow-management/diagnosis', 'parent' => 'workshop.setup', 'parent2' => 'workshop.workflow-management'],
            ['key' => 'workshop.workflow.repair-maintenance', 'label' => 'Repair & Maintenance', 'url' => 'workshop/workflow-management/repair-maintenance', 'parent' => 'workshop.setup', 'parent2' => 'workshop.workflow-management'],
            ['key' => 'workshop.workflow.spare-parts-usage', 'label' => 'Spare Parts Usage', 'url' => 'workshop/workflow-management/spare-parts-usage', 'parent' => 'workshop.setup', 'parent2' => 'workshop.workflow-management'],
            ['key' => 'workshop.workflow.labour-management', 'label' => 'Labour Management', 'url' => 'workshop/workflow-management/labour-management', 'parent' => 'workshop.setup', 'parent2' => 'workshop.workflow-management'],
            ['key' => 'workshop.workflow.job-completion', 'label' => 'Job Completion', 'url' => 'workshop/workflow-management/job-completion', 'parent' => 'workshop.setup', 'parent2' => 'workshop.workflow-management'],
            ['key' => 'workshop.workflow.quality-check', 'label' => 'Quality Check', 'url' => 'workshop/workflow-management/quality-check', 'parent' => 'workshop.setup', 'parent2' => 'workshop.workflow-management'],
            ['key' => 'workshop.workflow.job-history', 'label' => 'Job History', 'url' => 'workshop/workflow-management/job-history', 'parent' => 'workshop.setup', 'parent2' => 'workshop.workflow-management'],
        ];

        $this->upsertMenus($menus);
        $this->grantAdminPermissions(collect($menus)->pluck('key')->all());
    }

    public function down(): void
    {
        if (! Schema::hasTable('tbl_menus')) {
            return;
        }

        $keys = [
            'workshop.workflow-management',
            'workshop.workflow.job-card',
            'workshop.workflow.vehicle-inspection',
            'workshop.workflow.diagnosis',
            'workshop.workflow.repair-maintenance',
            'workshop.workflow.spare-parts-usage',
            'workshop.workflow.labour-management',
            'workshop.workflow.job-completion',
            'workshop.workflow.quality-check',
            'workshop.workflow.job-history',
        ];

        if (Schema::hasTable('user_type_menu_permissions')) {
            DB::table('user_type_menu_permissions')->whereIn('menu_key', $keys)->delete();
        }

        if (Schema::hasTable('user_menu_permissions')) {
            DB::table('user_menu_permissions')->whereIn('menu_key', $keys)->delete();
        }

        DB::table('tbl_menus')->whereIn('name', $keys)->delete();
    }

    private function upsertMenus(array $menus): void
    {
        $ids = [];
        $nextModuleId = ((int) DB::table('tbl_menus')->max('module_id')) + 1;

        foreach ($menus as $menu) {
            $parentId = $menu['parent']
                ? ($ids[$menu['parent']] ?? DB::table('tbl_menus')->where('name', $menu['parent'])->value('module_id'))
                : null;
            $parentId2 = !empty($menu['parent2'])
                ? ($ids[$menu['parent2']] ?? DB::table('tbl_menus')->where('name', $menu['parent2'])->value('module_id'))
                : null;

            $data = [
                'name' => $menu['key'],
                'label' => $menu['label'],
                'menu_icon' => null,
                'route_path' => $menu['url'],
                'parent_id' => $parentId,
                'is_menu' => true,
                'description' => $menu['label'],
                'is_dashboard' => 0,
                'collapse' => $menu['collapse'] ?? 0,
                'new_message' => 0,
            ];

            if (Schema::hasColumn('tbl_menus', 'parent_id2')) {
                $data['parent_id2'] = $parentId2;
            }

            $existingId = DB::table('tbl_menus')->where('name', $menu['key'])->value('module_id');

            if ($existingId) {
                DB::table('tbl_menus')->where('module_id', $existingId)->update($data);
                $ids[$menu['key']] = (int) $existingId;
            } else {
                DB::table('tbl_menus')->insert(array_merge(['module_id' => $nextModuleId], $data));
                $ids[$menu['key']] = $nextModuleId++;
            }
        }
    }

    private function grantAdminPermissions(array $menuKeys): void
    {
        if (! Schema::hasTable('tbl_users_privileges') || ! Schema::hasTable('user_type_menu_permissions')) {
            return;
        }

        $adminIds = DB::table('tbl_users_privileges')
            ->whereIn('privilege_name', ['Admin', 'Administrator'])
            ->pluck('id');

        foreach ($adminIds as $privilegeId) {
            foreach ($menuKeys as $menuKey) {
                DB::table('user_type_menu_permissions')->updateOrInsert(
                    ['privilege_id' => $privilegeId, 'menu_key' => $menuKey],
                    ['can_access' => true, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }
};
