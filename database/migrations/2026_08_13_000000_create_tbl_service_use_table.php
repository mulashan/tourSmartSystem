<?php
// database/migrations/2026_08_13_000000_create_tbl_service_use_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_service_use', function (Blueprint $table) {
            $table->increments('id');
            $table->date('requisition_date');
            $table->unsignedInteger('subdepartment_id');
            $table->unsignedBigInteger('officer_user_id');
            $table->string('reason', 255);
            $table->timestamps();

            $table->foreign('subdepartment_id')->references('Subdepartment_ID')->on('tbl_subdepartment');
            $table->foreign('officer_user_id')->references('id')->on('users');
        });

        Schema::create('tbl_service_use_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_use_id');
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('quantity');
            $table->timestamps();

            $table->foreign('service_use_id')->references('id')->on('tbl_service_use')->cascadeOnDelete();
            $table->foreign('item_id')->references('id')->on('tbl_items');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_service_use_items');
        Schema::dropIfExists('tbl_service_use');
    }
};