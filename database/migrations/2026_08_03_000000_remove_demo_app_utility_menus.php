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

        $keys = [
            'authentication',
            'apps.calendar',
            'apps.kanban',
            'apps.chat',
            'apps.contacts',
            'apps.files',
            'apps.email',
            'apps.todo',
            'apps.support',
            'interface.components',
            'interface.widgets',
            'interface.forms',
            'interface.tables',
            'interface.charts',
            'interface.icons',
            'utility.contact',
            'utility.invoices',
            'utility.invoices.list',
            'utility.invoices.view',
            'utility.pricing',
            'utility.faq',
            'utility.errors',
            'utility.timeline',
            'utility.search',
            'utility.blank',
        ];

        if (Schema::hasTable('user_type_menu_permissions')) {
            DB::table('user_type_menu_permissions')->whereIn('menu_key', $keys)->delete();
        }

        if (Schema::hasTable('user_menu_permissions')) {
            DB::table('user_menu_permissions')->whereIn('menu_key', $keys)->delete();
        }

        $moduleIds = DB::table('tbl_menus')->whereIn('name', $keys)->pluck('module_id');

        if ($moduleIds->isNotEmpty() && Schema::hasColumn('tbl_menus', 'parent_id2')) {
            DB::table('tbl_menus')->whereIn('parent_id2', $moduleIds)->update(['parent_id2' => null]);
        }

        if ($moduleIds->isNotEmpty()) {
            DB::table('tbl_menus')->whereIn('parent_id', $moduleIds)->update(['parent_id' => null]);
        }

        DB::table('tbl_menus')->whereIn('name', $keys)->delete();
    }

    public function down(): void
    {
        //
    }
};
