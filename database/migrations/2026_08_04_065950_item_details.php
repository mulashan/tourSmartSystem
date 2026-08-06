<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_requisition_items', function (Blueprint $table) {
            $table->string('item_details', 255)->nullable()->after('quantity_requested');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_requisition_items', function (Blueprint $table) {
            $table->dropColumn('item_details');
        });
    }
};