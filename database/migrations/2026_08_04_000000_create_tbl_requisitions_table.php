<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_requisitions', function (Blueprint $table) {
            $table->increments('id');
            $table->date('requisition_date');
            $table->unsignedInteger('requesting_subdepartment_id');
            $table->unsignedInteger('issuing_subdepartment_id');
            $table->unsignedBigInteger('officer_user_id');
            $table->string('description', 255);

            $table->string('status', 20)->default('draft'); // draft | pending_approval | approved

            $table->unsignedBigInteger('submitted_by_user_id')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->foreign('requesting_subdepartment_id')->references('Subdepartment_ID')->on('tbl_subdepartment');
            $table->foreign('issuing_subdepartment_id')->references('Subdepartment_ID')->on('tbl_subdepartment');
            $table->foreign('officer_user_id')->references('id')->on('users');
            $table->foreign('submitted_by_user_id')->references('id')->on('users');
            $table->foreign('approved_by_user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_requisitions');
    }
};