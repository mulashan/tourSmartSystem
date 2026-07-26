<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_company', function (Blueprint $table) {
            $table->string('Company_Logo', 255)->nullable()->after('Company_Name');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_company', function (Blueprint $table) {
            $table->dropColumn('Company_Logo');
        });
    }
};