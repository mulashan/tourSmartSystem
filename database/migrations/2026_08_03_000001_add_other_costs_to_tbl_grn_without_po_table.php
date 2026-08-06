<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_grn_without_po', function (Blueprint $table) {
            $table->string('vat_charges', 255)->nullable()->after('delivery_person');
            $table->string('transport_charges', 255)->nullable()->after('vat_charges');
            $table->string('labor_charges', 255)->nullable()->after('transport_charges');
            $table->string('bank_charges', 255)->nullable()->after('labor_charges');
            $table->string('freight_charges', 255)->nullable()->after('bank_charges');
            $table->string('other_charges', 255)->nullable()->after('freight_charges');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_grn_without_po', function (Blueprint $table) {
            $table->dropColumn(['vat_charges', 'transport_charges', 'labor_charges', 'bank_charges', 'freight_charges', 'other_charges']);
        });
    }
};