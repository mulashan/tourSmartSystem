<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_local_purchase_order_items', function (Blueprint $table) {
            $table->increments('lpo_item_id');
            $table->unsignedInteger('local_purchase_order_id');
            $table->unsignedInteger('Quantity_Required');
            $table->unsignedInteger('Containers_Required')->default(0);
            $table->unsignedInteger('Items_Per_Container_Required')->default(0);
            $table->double('Price')->nullable();
            $table->string('Remark', 120)->nullable();
            $table->unsignedInteger('Item_ID')->nullable();
            $table->unsignedInteger('Quantity_Supplied')->nullable();
            $table->unsignedInteger('Quantity_Received')->nullable();
            $table->unsignedBigInteger('quantity_rejected')->default(0);
            $table->unsignedInteger('Containers_Received')->default(0);
            $table->unsignedInteger('Items_Per_Container')->default(0);
            $table->double('Buying_Price')->nullable();
            $table->unsignedInteger('Grn_Purchase_Order_ID')->nullable();
            $table->string('Item_Status', 30)->default('active');
            $table->date('Expire_Date')->nullable();
            $table->string('Grn_Status', 20)->nullable();
            $table->boolean('was_pending')->default(false);
            $table->text('rejection_reason')->nullable();
            $table->integer('Remain_Balance')->default(0);
            $table->timestamps();

            $table->foreign('local_purchase_order_id')->references('local_purchase_order_id')->on('tbl_local_purchase_order')->cascadeOnDelete();
            $table->foreign('Item_ID')->references('id')->on('tbl_items');
            $table->index(['local_purchase_order_id', 'Item_ID']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_local_purchase_order_items');
    }
};