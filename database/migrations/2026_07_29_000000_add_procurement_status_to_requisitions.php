<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_store_requisition_items', function (Blueprint $table) {
            $table->string('procurement_status', 20)->default('pending')->after('item_details'); // pending | ordered | rejected
            $table->string('rejection_reason', 255)->nullable()->after('procurement_status');
        });

        Schema::table('tbl_store_requisitions', function (Blueprint $table) {
            $table->string('procurement_status', 20)->default('pending')->after('status'); // pending | processed | rejected
        });
    }

    public function down(): void
    {
        Schema::table('tbl_store_requisition_items', function (Blueprint $table) {
            $table->dropColumn(['procurement_status', 'rejection_reason']);
        });

        Schema::table('tbl_store_requisitions', function (Blueprint $table) {
            $table->dropColumn('procurement_status');
        });
    }
};