<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tbl_menus') || Schema::hasColumn('tbl_menus', 'parent_id2')) {
            return;
        }

        Schema::table('tbl_menus', function (Blueprint $table) {
            $table->integer('parent_id2')->nullable()->after('parent_id');
            $table->index('parent_id2');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('tbl_menus') || ! Schema::hasColumn('tbl_menus', 'parent_id2')) {
            return;
        }

        Schema::table('tbl_menus', function (Blueprint $table) {
            $table->dropIndex(['parent_id2']);
            $table->dropColumn('parent_id2');
        });
    }
};
