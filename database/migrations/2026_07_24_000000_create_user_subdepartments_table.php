<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_user_subdepartments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('subdepartment_id');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('subdepartment_id')->references('Subdepartment_ID')->on('tbl_subdepartment')->cascadeOnDelete();
            $table->unique(['user_id', 'subdepartment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_user_subdepartments');
    }
};