<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_grn_batches', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('grn_item_id');
            $table->string('batch_number', 100);
            $table->unsignedInteger('units');
            $table->unsignedInteger('items_per_unit');
            $table->unsignedInteger('quantity');
            $table->double('buying_price');
            $table->date('manufacture_date');
            $table->date('expiry_date');
            $table->date('received_date');
            $table->timestamps();

            $table->foreign('grn_item_id')->references('id')->on('tbl_grn_items')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_grn_batches');
    }
};