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

        $reports = DB::table('tbl_menus')->where('name', 'workshop.reports')->first();

        if (! $reports) {
            return;
        }

        $nextModuleId = ((int) DB::table('tbl_menus')->max('module_id')) + 1;

        DB::table('tbl_menus')
            ->where('module_id', $reports->module_id)
            ->update(['module_id' => $nextModuleId]);
    }

    public function down(): void
    {
        // Preserve the current menu ordering on rollback.
    }
};
