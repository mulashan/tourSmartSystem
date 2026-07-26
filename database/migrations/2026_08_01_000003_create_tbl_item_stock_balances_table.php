<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_item_stock_balances', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('subdepartment_id');
            $table->unsignedInteger('quantity_balance')->default(0);
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('tbl_items');
            $table->foreign('subdepartment_id')->references('Subdepartment_ID')->on('tbl_subdepartment');
            $table->unique(['item_id', 'subdepartment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_item_stock_balances');
    }
};