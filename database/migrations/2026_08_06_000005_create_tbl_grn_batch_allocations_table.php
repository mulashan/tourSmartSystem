<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_grn_batch_allocations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('grn_item_id');
            $table->unsignedInteger('stock_batch_id');
            $table->unsignedInteger('quantity_allocated');
            $table->timestamps();

            $table->foreign('grn_item_id')->references('id')->on('tbl_grn_against_issue_note_items')->cascadeOnDelete();
            $table->foreign('stock_batch_id')->references('id')->on('tbl_stock_batches');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_grn_batch_allocations');
    }
};