<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_branches', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('branch_id');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('branch_id')->references('Branch_ID')->on('tbl_branches')->cascadeOnDelete();
            $table->unique(['user_id', 'branch_id']);
        });

        // Carry forward each user's existing single branch_id as their first assigned branch.
        DB::table('users')->whereNotNull('branch_id')->get(['id', 'branch_id'])->each(function ($user) {
            DB::table('user_branches')->updateOrInsert(['user_id' => $user->id, 'branch_id' => $user->branch_id]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_branches');
    }
};