<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_stock_ledger', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('subdepartment_id');
            $table->string('movement_type', 30); // grn_receipt | issue | adjustment | transfer_in | transfer_out
            $table->string('reference_type', 50)->nullable(); // e.g. 'grn', 'issue_note'
            $table->unsignedInteger('reference_id')->nullable();
            $table->unsignedInteger('quantity_in')->default(0);
            $table->unsignedInteger('quantity_out')->default(0);
            $table->unsignedInteger('balance_after');
            $table->unsignedInteger('grn_batch_id')->nullable();
            $table->unsignedBigInteger('created_by_user_id');
            $table->timestamp('moved_at');
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('tbl_items');
            $table->foreign('subdepartment_id')->references('Subdepartment_ID')->on('tbl_subdepartment');
            $table->foreign('grn_batch_id')->references('id')->on('tbl_grn_batches');
            $table->foreign('created_by_user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_stock_ledger');
    }
};