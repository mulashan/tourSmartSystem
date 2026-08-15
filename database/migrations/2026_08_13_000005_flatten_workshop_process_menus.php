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

        $workshopId = DB::table('tbl_menus')->where('name', 'workshop.setup')->value('module_id');

        if (! $workshopId) {
            return;
        }

        DB::table('tbl_menus')->whereIn('name', [
            'workshop.workflow-management',
            'workshop.job-cards',
            'workshop.open-jobs',
            'workshop.completed-jobs',
            'workshop.closed-jobs',
            'workshop.invoices',
        ])->delete();

        DB::table('tbl_menus')
            ->where('name', 'like', 'workshop.workflow.%')
            ->update([
                'parent_id' => $workshopId,
                'parent_id2' => null,
            ]);
    }

    public function down(): void
    {
        // Menu flattening is intentionally not reversed automatically to avoid
        // reintroducing deprecated workshop navigation entries.
    }
};
