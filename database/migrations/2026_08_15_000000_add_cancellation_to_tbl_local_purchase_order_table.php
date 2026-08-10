<?php
// database/migrations/2026_08_15_000000_add_cancellation_to_tbl_local_purchase_order_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_local_purchase_order', function (Blueprint $table) {
            $table->unsignedBigInteger('cancelled_by_user_id')->nullable()->after('approved_at');
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by_user_id');
            $table->string('cancel_reason', 255)->nullable()->after('cancelled_at');

            $table->foreign('cancelled_by_user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_local_purchase_order', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by_user_id']);
            $table->dropColumn(['cancelled_by_user_id', 'cancelled_at', 'cancel_reason']);
        });
    }
};