<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tbl_employee') || Schema::hasColumn('tbl_employee', 'photo')) {
            return;
        }

        Schema::table('tbl_employee', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('employee_signature');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('tbl_employee') || ! Schema::hasColumn('tbl_employee', 'photo')) {
            return;
        }

        Schema::table('tbl_employee', function (Blueprint $table) {
            $table->dropColumn('photo');
        });
    }
};
