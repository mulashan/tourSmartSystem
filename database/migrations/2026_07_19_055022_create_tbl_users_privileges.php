<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tbl_users_privileges', function (Blueprint $table) {
             $table->id();                    // BIGSERIAL PRIMARY KEY
            $table->string('privilege_name');
            $table->string('privilege_desc');
            $table->integer('access_level_id')->default(1);
            $table->boolean('priv_status')->default(true);
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_users_privileges');
    }
};
