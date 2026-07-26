<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_lpo_status_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('local_purchase_order_id');
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            $table->unsignedBigInteger('changed_by_user_id');
            $table->string('remark', 255)->nullable();
            $table->timestamp('changed_at');
            $table->timestamps();

            $table->foreign('local_purchase_order_id')->references('local_purchase_order_id')->on('tbl_local_purchase_order')->cascadeOnDelete();
            $table->foreign('changed_by_user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_lpo_status_history');
    }
};