<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('repair_orders')) {
            return;
        }

        Schema::table('repair_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('repair_orders', 'maintenance_location')) {
                $table->string('maintenance_location', 30)->default('in_house')->after('description');
            }

            if (! Schema::hasColumn('repair_orders', 'vendor_name')) {
                $table->string('vendor_name')->nullable()->after('maintenance_location');
            }

            if (! Schema::hasColumn('repair_orders', 'external_cost')) {
                $table->decimal('external_cost', 12, 2)->default(0)->after('vendor_name');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('repair_orders')) {
            return;
        }

        Schema::table('repair_orders', function (Blueprint $table) {
            foreach (['external_cost', 'vendor_name', 'maintenance_location'] as $column) {
                if (Schema::hasColumn('repair_orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
