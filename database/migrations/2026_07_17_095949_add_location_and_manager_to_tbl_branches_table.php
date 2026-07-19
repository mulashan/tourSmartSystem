<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tbl_branches', function (Blueprint $table) {
            //
            $table->string('Location', 255)->nullable()->after('Branch_Name');
            $table->string('Manager', 255)->nullable()->after('Location');
            $table->string('status', 11)->nullable()->after('Company_ID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_branches', function (Blueprint $table) {
            //
                $table->dropColumn(['Location', 'Manager', 'status']);
        });
    }
};
