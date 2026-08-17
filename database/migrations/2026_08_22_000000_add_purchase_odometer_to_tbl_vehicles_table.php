<?php
// database/migrations/2026_08_22_000000_add_purchase_odometer_to_tbl_vehicles_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_vehicles', function (Blueprint $table) {
            $table->unsignedInteger('purchase_odometer')->nullable()->after('current_odometer');
        });

        // Backfill existing rows so historical vehicles have a baseline too.
        DB::table('tbl_vehicles')->whereNull('purchase_odometer')->update([
            'purchase_odometer' => DB::raw('current_odometer'),
        ]);
    }

    public function down(): void
    {
        Schema::table('tbl_vehicles', function (Blueprint $table) {
            $table->dropColumn('purchase_odometer');
        });
    }
};