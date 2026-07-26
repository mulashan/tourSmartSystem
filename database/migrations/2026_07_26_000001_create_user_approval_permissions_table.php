<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_approval_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('approval_permission_id');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('approval_permission_id')->references('id')->on('tbl_approval_permissions')->cascadeOnDelete();
            $table->unique(['user_id', 'approval_permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_approval_permissions');
    }
};