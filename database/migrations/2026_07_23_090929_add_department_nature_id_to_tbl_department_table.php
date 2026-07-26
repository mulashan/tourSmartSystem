<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_department', function (Blueprint $table) {
            $table->unsignedInteger('department_nature_id')->nullable()->after('Branch_ID');
            $table->string('Department_Location', 100)->nullable()->change();

            $table->foreign('department_nature_id')->references('id')->on('tbl_department_nature');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_department', function (Blueprint $table) {
            $table->dropForeign(['department_nature_id']);
            $table->dropColumn('department_nature_id');
            $table->string('Department_Location', 100)->nullable(false)->change();
        });
    }
};