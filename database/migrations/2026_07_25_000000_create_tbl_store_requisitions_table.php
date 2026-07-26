<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_store_requisitions', function (Blueprint $table) {
            $table->increments('id');
            $table->date('order_date');
            $table->unsignedInteger('subdepartment_id');
            $table->unsignedBigInteger('prepared_by_user_id');
            $table->string('priority_status', 15)->default('normal'); // normal | emergency
            $table->string('emergency_reason', 255)->nullable();
            $table->string('order_description', 255)->nullable();
            $table->boolean('is_user_store_order')->default(false);
            $table->string('status', 20)->default('pending'); // pending | approved
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('subdepartment_id')->references('Subdepartment_ID')->on('tbl_subdepartment');
            $table->foreign('prepared_by_user_id')->references('id')->on('users');
            $table->foreign('approved_by_user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_store_requisitions');
    }
};