<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_subdepartment', function (Blueprint $table) {
            $table->increments('Subdepartment_ID');
            $table->string('Subdepartment_Name', 100);
            $table->unsignedInteger('Department_ID');
            $table->string('status', 15)->default('active');

            $table->foreign('Department_ID')->references('Department_ID')->on('tbl_department');
            $table->unique(['Department_ID', 'Subdepartment_Name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_subdepartment');
    }
};