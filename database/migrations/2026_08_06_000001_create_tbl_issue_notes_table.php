<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_issue_notes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('requisition_id');
            $table->unsignedBigInteger('officer_user_id');
            $table->date('issue_date');
            $table->string('status', 20)->default('pending_approval'); // pending_approval | approved
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('requisition_id')->references('id')->on('tbl_requisitions');
            $table->foreign('officer_user_id')->references('id')->on('users');
            $table->foreign('approved_by_user_id')->references('id')->on('users');
            $table->unique('requisition_id'); // one issue note per requisition
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_issue_notes');
    }
};