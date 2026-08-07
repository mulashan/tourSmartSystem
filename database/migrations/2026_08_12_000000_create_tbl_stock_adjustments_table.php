<?php
// database/migrations/2026_08_12_000000_create_tbl_stock_adjustments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_stock_adjustments', function (Blueprint $table) {
            $table->increments('id');
            $table->date('adjustment_date');
            $table->unsignedInteger('subdepartment_id');
            $table->unsignedBigInteger('officer_user_id');
            $table->string('description', 255);
            $table->string('reason', 30); // add_stock_balance | expired_dump_broken

            $table->string('status', 20)->default('draft'); // draft|pending_approval|approved

            $table->unsignedBigInteger('submitted_by_user_id')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->foreign('subdepartment_id')->references('Subdepartment_ID')->on('tbl_subdepartment');
            $table->foreign('officer_user_id')->references('id')->on('users');
            $table->foreign('submitted_by_user_id')->references('id')->on('users');
            $table->foreign('approved_by_user_id')->references('id')->on('users');
        });

        Schema::create('tbl_stock_adjustment_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('adjustment_id');
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('quantity'); // deduct mode: qty to remove; add mode: sum of batches
            $table->timestamps();

            $table->foreign('adjustment_id')->references('id')->on('tbl_stock_adjustments')->cascadeOnDelete();
            $table->foreign('item_id')->references('id')->on('tbl_items');
        });

        // Used only when reason = add_stock_balance
        Schema::create('tbl_stock_adjustment_batches', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('adjustment_item_id');
            $table->string('batch_number', 100);
            $table->unsignedInteger('units');
            $table->unsignedInteger('items_per_unit');
            $table->unsignedInteger('quantity');
            $table->double('buying_price');
            $table->date('manufacture_date');
            $table->date('expiry_date');
            $table->date('received_date');
            $table->timestamps();

            $table->foreign('adjustment_item_id')->references('id')->on('tbl_stock_adjustment_items')->cascadeOnDelete();
        });

        // Used only when reason = expired_dump_broken (FEFO consumption, computed at approval)
        Schema::create('tbl_stock_adjustment_batch_allocations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('adjustment_item_id');
            $table->unsignedInteger('stock_batch_id');
            $table->unsignedInteger('quantity_allocated');
            $table->timestamps();

            $table->foreign('adjustment_item_id')->references('id')->on('tbl_stock_adjustment_items')->cascadeOnDelete();
            $table->foreign('stock_batch_id')->references('id')->on('tbl_stock_batches');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_stock_adjustment_batch_allocations');
        Schema::dropIfExists('tbl_stock_adjustment_batches');
        Schema::dropIfExists('tbl_stock_adjustment_items');
        Schema::dropIfExists('tbl_stock_adjustments');
    }
};