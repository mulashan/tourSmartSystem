<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_store_requisition_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('store_requisition_id');
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('units')->default(1);
            $table->unsignedInteger('items_per_unit')->default(1);
            $table->unsignedInteger('quantity')->default(0);
            $table->string('item_details', 255)->nullable();
            $table->timestamps();

            $table->foreign('store_requisition_id')->references('id')->on('tbl_store_requisitions')->cascadeOnDelete();
            $table->foreign('item_id')->references('id')->on('tbl_items');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_store_requisition_items');
    }
};