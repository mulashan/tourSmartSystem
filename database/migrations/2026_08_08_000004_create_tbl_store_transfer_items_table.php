<?php
// database/migrations/2026_08_08_000004_create_tbl_store_transfer_items_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tbl_store_transfer_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('transfer_id');
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('quantity');
            $table->timestamps();

            $table->foreign('transfer_id')->references('id')->on('tbl_store_transfers')->cascadeOnDelete();
            $table->foreign('item_id')->references('id')->on('tbl_items');
        });
    }
    public function down(): void { Schema::dropIfExists('tbl_store_transfer_items'); }
};