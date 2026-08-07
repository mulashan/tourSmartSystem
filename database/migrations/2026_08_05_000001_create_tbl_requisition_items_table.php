<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_requisition_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('requisition_id');
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('quantity_requested');
            $table->timestamps();

            $table->foreign('requisition_id')->references('id')->on('tbl_requisitions')->cascadeOnDelete();
            $table->foreign('item_id')->references('id')->on('tbl_items');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_requisition_items');
    }
};