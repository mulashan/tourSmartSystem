<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tbl_menus')) {
            return;
        }

        Schema::create('tbl_menus', function (Blueprint $table) {
            $table->increments('module_id');
            $table->string('name', 80);
            $table->string('label', 50);
            $table->string('menu_icon', 80)->nullable();
            $table->string('route_path', 100)->nullable();
            $table->integer('parent_id')->nullable();
            $table->boolean('is_menu')->default(true);
            $table->string('description', 400)->nullable();
            $table->integer('is_dashboard')->default(0);
            $table->integer('collapse')->default(0);
            $table->integer('new_message')->default(0);

            $table->unique('name');
            $table->index('parent_id');
            $table->index('is_menu');
        });
    }

    public function down(): void
    {
        // Keep existing menu data safe. Drop tbl_menus manually only if you are sure it was created for this app.
    }
};
