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

        DB::table('tbl_menus')->where('name', 'workshop.workflow-management')->update([
            'route_path' => null,
            'collapse' => 1,
        ]);

        foreach ($this->processRoutes() as $key => $route) {
            DB::table('tbl_menus')->where('name', $key)->update(['route_path' => $route]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('tbl_menus')) {
            return;
        }

        DB::table('tbl_menus')->where('name', 'workshop.workflow-management')->update([
            'route_path' => 'workshop/workflow-management',
            'collapse' => 1,
        ]);

        foreach ($this->processAnchors() as $key => $route) {
            DB::table('tbl_menus')->where('name', $key)->update(['route_path' => $route]);
        }
    }

    private function processRoutes(): array
    {
        return [
            'workshop.workflow.job-card' => 'workshop/workflow-management/job-card',
            'workshop.workflow.vehicle-inspection' => 'workshop/workflow-management/vehicle-inspection',
            'workshop.workflow.diagnosis' => 'workshop/workflow-management/diagnosis',
            'workshop.workflow.repair-maintenance' => 'workshop/workflow-management/repair-maintenance',
            'workshop.workflow.spare-parts-usage' => 'workshop/workflow-management/spare-parts-usage',
            'workshop.workflow.labour-management' => 'workshop/workflow-management/labour-management',
            'workshop.workflow.job-completion' => 'workshop/workflow-management/job-completion',
            'workshop.workflow.quality-check' => 'workshop/workflow-management/quality-check',
            'workshop.workflow.job-history' => 'workshop/workflow-management/job-history',
        ];
    }

    private function processAnchors(): array
    {
        return [
            'workshop.workflow.job-card' => 'workshop/workflow-management#job-card',
            'workshop.workflow.vehicle-inspection' => 'workshop/workflow-management#vehicle-inspection',
            'workshop.workflow.diagnosis' => 'workshop/workflow-management#diagnosis',
            'workshop.workflow.repair-maintenance' => 'workshop/workflow-management#repair-maintenance',
            'workshop.workflow.spare-parts-usage' => 'workshop/workflow-management#spare-parts-usage',
            'workshop.workflow.labour-management' => 'workshop/workflow-management#labour-management',
            'workshop.workflow.job-completion' => 'workshop/workflow-management#job-completion',
            'workshop.workflow.quality-check' => 'workshop/workflow-management#quality-check',
            'workshop.workflow.job-history' => 'workshop/workflow-management#job-history',
        ];
    }
};
