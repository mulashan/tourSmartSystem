<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_type_menu_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('privilege_id');
            $table->string('menu_key', 100);
            $table->boolean('can_access')->default(true);
            $table->timestamps();

            $table->unique(['privilege_id', 'menu_key']);
            $table->foreign('privilege_id')->references('id')->on('tbl_users_privileges')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_type_menu_permissions');
    }
};
