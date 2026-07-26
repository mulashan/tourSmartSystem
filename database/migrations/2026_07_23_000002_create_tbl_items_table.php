<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('product_name', 150);
            $table->string('product_code_prefix', 30)->nullable();
            $table->string('product_code', 60)->nullable()->unique();
            $table->integer('unit_of_measure_id')->nullable();
            $table->integer('item_category_id');
            $table->string('status', 15)->default('active');
            $table->integer('reorder_level')->nullable();
            $table->integer('minimum_reorder_level')->nullable();
            $table->integer('maximum_reorder_level')->nullable();
            $table->timestamps();

            $table->foreign('unit_of_measure_id')->references('id')->on('tbl_lookups')->nullOnDelete();
            $table->foreign('item_category_id')->references('id')->on('tbl_lookups');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_items');
    }
};