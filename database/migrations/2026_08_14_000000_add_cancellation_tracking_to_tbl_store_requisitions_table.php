<?php
// database/migrations/2026_08_14_000000_add_cancellation_tracking_to_tbl_store_requisitions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_store_requisitions', function (Blueprint $table) {
            $table->unsignedInteger('procurement_subdepartment_id')->nullable()->after('procurement_status');
            $table->unsignedBigInteger('cancelled_by_user_id')->nullable()->after('rejection_reason');
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by_user_id');

            $table->foreign('procurement_subdepartment_id')->references('Subdepartment_ID')->on('tbl_subdepartment');
            $table->foreign('cancelled_by_user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_store_requisitions', function (Blueprint $table) {
            $table->dropForeign(['procurement_subdepartment_id']);
            $table->dropForeign(['cancelled_by_user_id']);
            $table->dropColumn(['procurement_subdepartment_id', 'cancelled_by_user_id', 'cancelled_at']);
        });
    }
};