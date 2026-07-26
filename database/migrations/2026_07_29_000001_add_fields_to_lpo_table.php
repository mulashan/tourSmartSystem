<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_local_purchase_order', function (Blueprint $table) {
            $table->string('currency_type', 10)->default('Tshs')->after('supplier_id');
            $table->string('requisition_description', 255)->nullable()->after('currency_type');
            $table->string('rejection_reason', 255)->nullable()->after('status');
             $table->unsignedInteger('supplier_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tbl_local_purchase_order', function (Blueprint $table) {
            $table->dropColumn(['currency_type', 'requisition_description', 'rejection_reason']);
        });
    }
};