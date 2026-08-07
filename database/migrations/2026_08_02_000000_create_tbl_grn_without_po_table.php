<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_grn_without_po', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('subdepartment_id');
            $table->unsignedInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('created_by_user_id');

            $table->string('purchase_description', 255)->nullable();
            $table->string('delivery_note_number', 255)->nullable();
            $table->string('delivery_note_attachment', 255)->nullable();
            $table->string('invoice_number', 255)->nullable();
            $table->string('invoice_attachment', 255)->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('delivery_person', 255)->nullable();

            $table->string('status', 20)->default('pending_approval'); // pending_approval | approved

            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->foreign('subdepartment_id')->references('Subdepartment_ID')->on('tbl_subdepartment');
            $table->foreign('supplier_id')->references('id')->on('tbl_suppliers');
            $table->foreign('created_by_user_id')->references('id')->on('users');
            $table->foreign('approved_by_user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_grn_without_po');
    }
};