<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'customers',
        'vehicles',
        'job_cards',
        'repair_orders',
        'mechanics',
        'diagnosis',
        'job_mechanics',
        'labour_entries',
        'parts_used',
        'job_completion',
        'quality_checks',
        'invoices',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'created_by')) {
                    $table->unsignedBigInteger('created_by')->default(1)->after('updated_at');
                }

                if (! Schema::hasColumn($tableName, 'updated_by')) {
                    $table->unsignedBigInteger('updated_by')->default(0)->after('created_by');
                }
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables) as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'updated_by')) {
                    $table->dropColumn('updated_by');
                }

                if (Schema::hasColumn($tableName, 'created_by')) {
                    $table->dropColumn('created_by');
                }
            });
        }
    }
};
