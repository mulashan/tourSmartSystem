<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_grn_against_issue_note', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('issue_note_id');
            $table->unsignedBigInteger('created_by_user_id');
            $table->date('receipt_date');
            $table->string('status', 20)->default('pending_approval'); // pending_approval | approved
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('issue_note_id')->references('id')->on('tbl_issue_notes');
            $table->foreign('created_by_user_id')->references('id')->on('users');
            $table->foreign('approved_by_user_id')->references('id')->on('users');
            $table->unique('issue_note_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_grn_against_issue_note');
    }
};