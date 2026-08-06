<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_stock_batches', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('subdepartment_id');
            $table->string('batch_number', 100);
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date');
            $table->double('buying_price');
            $table->unsignedInteger('quantity_received');
            $table->unsignedInteger('quantity_remaining');
            $table->string('source_type', 40); // grn_against_po | grn_without_po | grn_open_balance | grn_against_issue_note
            $table->unsignedInteger('source_id');
            $table->date('received_date');
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('tbl_items');
            $table->foreign('subdepartment_id')->references('Subdepartment_ID')->on('tbl_subdepartment');
            $table->index(['item_id', 'subdepartment_id', 'expiry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_stock_batches');
    }
};