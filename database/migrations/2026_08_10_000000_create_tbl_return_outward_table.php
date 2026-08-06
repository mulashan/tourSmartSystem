<?php
// database/migrations/2026_08_10_000000_create_tbl_return_outward_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_return_outward', function (Blueprint $table) {
            $table->increments('id');
            $table->date('transaction_date');
            $table->unsignedInteger('subdepartment_id');
            $table->unsignedInteger('supplier_id');
            $table->unsignedBigInteger('posted_by_user_id');

            $table->string('status', 20)->default('draft'); // draft|pending_approval|approved

            $table->unsignedBigInteger('submitted_by_user_id')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->foreign('subdepartment_id')->references('Subdepartment_ID')->on('tbl_subdepartment');
            $table->foreign('supplier_id')->references('id')->on('tbl_suppliers');
            $table->foreign('posted_by_user_id')->references('id')->on('users');
            $table->foreign('submitted_by_user_id')->references('id')->on('users');
            $table->foreign('approved_by_user_id')->references('id')->on('users');
        });

        Schema::create('tbl_return_outward_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('return_id');
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('quantity');
            $table->timestamps();

            $table->foreign('return_id')->references('id')->on('tbl_return_outward')->cascadeOnDelete();
            $table->foreign('item_id')->references('id')->on('tbl_items');
        });

        Schema::create('tbl_return_outward_batch_allocations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('return_item_id');
            $table->unsignedInteger('stock_batch_id');
            $table->unsignedInteger('quantity_allocated');
            $table->timestamps();

            $table->foreign('return_item_id')->references('id')->on('tbl_return_outward_items')->cascadeOnDelete();
            $table->foreign('stock_batch_id')->references('id')->on('tbl_stock_batches');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_return_outward_batch_allocations');
        Schema::dropIfExists('tbl_return_outward_items');
        Schema::dropIfExists('tbl_return_outward');
    }
};