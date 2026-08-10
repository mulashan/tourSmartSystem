<?php
// database/migrations/2026_08_16_000000_add_session_timeout_to_tbl_branches_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_branches', function (Blueprint $table) {
            $table->unsignedInteger('session_timeout_minutes')->default(30)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_branches', function (Blueprint $table) {
            $table->dropColumn('session_timeout_minutes');
        });
    }
};