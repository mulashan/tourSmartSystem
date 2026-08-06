<?php
// database/migrations/2026_08_08_000005_create_tbl_store_transfer_batch_allocations_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tbl_store_transfer_batch_allocations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('transfer_item_id');
            $table->unsignedInteger('stock_batch_id');
            $table->unsignedInteger('quantity_allocated');
            $table->timestamps();

            $table->foreign('transfer_item_id')->references('id')->on('tbl_store_transfer_items')->cascadeOnDelete();
            $table->foreign('stock_batch_id')->references('id')->on('tbl_stock_batches');
        });
    }
    public function down(): void { Schema::dropIfExists('tbl_store_transfer_batch_allocations'); }
};