<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_local_purchase_order', function (Blueprint $table) {
            $table->unsignedInteger('procurement_subdepartment_id')->nullable()->after('created_by_user_id');
            $table->foreign('procurement_subdepartment_id')->references('Subdepartment_ID')->on('tbl_subdepartment');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_local_purchase_order', function (Blueprint $table) {
            $table->dropForeign(['procurement_subdepartment_id']);
            $table->dropColumn('procurement_subdepartment_id');
        });
    }
};