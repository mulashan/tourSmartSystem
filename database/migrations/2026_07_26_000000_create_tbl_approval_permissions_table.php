<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_approval_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key', 60)->unique();   // e.g. 'store_ordering_approval'
            $table->string('label', 150);
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_approval_permissions');
    }
};