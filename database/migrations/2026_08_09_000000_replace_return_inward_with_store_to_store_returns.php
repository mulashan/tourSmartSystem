<?php
// database/migrations/2026_08_09_000000_replace_return_inward_with_store_to_store_returns.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // These existed from the earlier, now-superseded design.
        Schema::dropIfExists('tbl_return_inward_batches');
        Schema::dropIfExists('tbl_return_inward_items');
        Schema::dropIfExists('tbl_return_inward');

        Schema::create('tbl_returns', function (Blueprint $table) {
            $table->increments('id');
            $table->date('return_date');
            $table->unsignedInteger('from_subdepartment_id'); // Store Returning
            $table->unsignedInteger('to_subdepartment_id');   // Store Receiving
            $table->unsignedBigInteger('posted_by_user_id');
            $table->string('description', 255);

            $table->string('status', 20)->default('draft'); // draft|pending_approval|pending_receipt|completed

            $table->unsignedBigInteger('submitted_by_user_id')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('received_by_user_id')->nullable();
            $table->timestamp('received_at')->nullable();

            $table->timestamps();

            $table->foreign('from_subdepartment_id')->references('Subdepartment_ID')->on('tbl_subdepartment');
            $table->foreign('to_subdepartment_id')->references('Subdepartment_ID')->on('tbl_subdepartment');
            $table->foreign('posted_by_user_id')->references('id')->on('users');
            $table->foreign('submitted_by_user_id')->references('id')->on('users');
            $table->foreign('approved_by_user_id')->references('id')->on('users');
            $table->foreign('received_by_user_id')->references('id')->on('users');
        });

        Schema::create('tbl_return_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('return_id');
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('quantity');
            $table->timestamps();

            $table->foreign('return_id')->references('id')->on('tbl_returns')->cascadeOnDelete();
            $table->foreign('item_id')->references('id')->on('tbl_items');
        });

        Schema::create('tbl_return_batch_allocations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('return_item_id');
            $table->unsignedInteger('stock_batch_id');
            $table->unsignedInteger('quantity_allocated');
            $table->timestamps();

            $table->foreign('return_item_id')->references('id')->on('tbl_return_items')->cascadeOnDelete();
            $table->foreign('stock_batch_id')->references('id')->on('tbl_stock_batches');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_return_batch_allocations');
        Schema::dropIfExists('tbl_return_items');
        Schema::dropIfExists('tbl_returns');
    }
};