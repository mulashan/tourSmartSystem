<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
        Schema::create('tbl_lookups', function (Blueprint $table) {
            $table->integer('id', true, false);
            $table->string('type', 50)->index();   // 'title', 'rank', 'measuring_unit', ...
            $table->string('name', 150);
            $table->string('code', 30)->nullable();
            $table->string('description', 255)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['type', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_lookups');
    }
};