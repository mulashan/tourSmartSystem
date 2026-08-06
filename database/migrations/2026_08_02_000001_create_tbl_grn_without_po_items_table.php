<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_grn_without_po_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('grn_id');
            $table->unsignedInteger('item_id');
            $table->string('remarks', 255)->nullable();
            $table->timestamps();

            $table->foreign('grn_id')->references('id')->on('tbl_grn_without_po')->cascadeOnDelete();
            $table->foreign('item_id')->references('id')->on('tbl_items');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_grn_without_po_items');
    }
};