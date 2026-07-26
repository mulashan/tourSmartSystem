<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_local_purchase_order', function (Blueprint $table) {
            $table->increments('local_purchase_order_id');
            $table->unsignedInteger('store_requisition_id')->nullable();
            $table->unsignedInteger('supplier_id');
            $table->unsignedBigInteger('created_by_user_id');
            $table->date('order_date');
            $table->string('status', 20)->default('draft'); // draft | pending_approval | approved

            $table->string('vat_charges', 255)->nullable();
            $table->string('labor_charges', 255)->nullable();
            $table->string('transport_charges', 255)->nullable();
            $table->string('freight_charges', 255)->nullable();
            $table->string('bank_charges', 255)->nullable();
            $table->string('other_charges', 255)->nullable();

            $table->unsignedBigInteger('submitted_by_user_id')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->foreign('store_requisition_id')->references('id')->on('tbl_store_requisitions');
            $table->foreign('supplier_id')->references('id')->on('tbl_suppliers');
            $table->foreign('created_by_user_id')->references('id')->on('users');
            $table->foreign('submitted_by_user_id')->references('id')->on('users');
            $table->foreign('approved_by_user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_local_purchase_order');
    }
};