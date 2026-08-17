<?php
// database/migrations/2026_08_21_000000_change_expected_return_to_date_on_tbl_gate_passes.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_gate_passes', function (Blueprint $table) {
            $table->date('expected_return')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tbl_gate_passes', function (Blueprint $table) {
            $table->dateTime('expected_return')->nullable()->change();
        });
    }
};