<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_grn_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('grn_id');
            $table->unsignedInteger('lpo_item_id');
            $table->unsignedInteger('item_id');
            $table->string('remarks', 255)->nullable();
            $table->timestamps();

            $table->foreign('grn_id')->references('Grn_Purchase_Order_ID')->on('tbl_grn_purchase_order')->cascadeOnDelete();
            $table->foreign('lpo_item_id')->references('lpo_item_id')->on('tbl_local_purchase_order_items');
            $table->foreign('item_id')->references('id')->on('tbl_items');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_grn_items');
    }
};