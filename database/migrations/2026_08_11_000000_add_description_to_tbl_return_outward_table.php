<?php
// database/migrations/2026_08_11_000000_add_description_to_tbl_return_outward_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_return_outward', function (Blueprint $table) {
            $table->string('description', 255)->nullable()->after('supplier_id');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_return_outward', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};