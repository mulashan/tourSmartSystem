<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_grn_purchase_order', function (Blueprint $table) {
            $table->increments('Grn_Purchase_Order_ID');
            $table->unsignedInteger('local_purchase_order_id');
            $table->unsignedInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('created_by_user_id');
            $table->unsignedInteger('Sub_Department_ID');

            $table->string('Purchase_Description', 255)->nullable();
            $table->string('Delivery_Note_Number', 255)->nullable();
            $table->string('Delivery_Note_Attachment', 255)->nullable();
            $table->string('Invoice_Number', 255)->nullable();
            $table->string('Invoice_Attachment', 255)->nullable();
            $table->date('Delivery_Date')->nullable();
            $table->string('Delivery_Person', 255)->nullable();

            $table->string('status', 20)->default('draft'); // draft | pending_approval | approved

            $table->unsignedBigInteger('submitted_by_user_id')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->foreign('local_purchase_order_id')->references('local_purchase_order_id')->on('tbl_local_purchase_order');
            $table->foreign('supplier_id')->references('id')->on('tbl_suppliers');
            $table->foreign('created_by_user_id')->references('id')->on('users');
            $table->foreign('Sub_Department_ID')->references('Subdepartment_ID')->on('tbl_subdepartment');
            $table->foreign('submitted_by_user_id')->references('id')->on('users');
            $table->foreign('approved_by_user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_grn_purchase_order');
    }
};