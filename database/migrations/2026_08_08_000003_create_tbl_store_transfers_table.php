<?php
// database/migrations/2026_08_08_000003_create_tbl_store_transfers_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tbl_store_transfers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('from_subdepartment_id');
            $table->unsignedInteger('to_subdepartment_id');
            $table->unsignedBigInteger('created_by_user_id');
            $table->date('transfer_date');
            $table->string('description', 255)->nullable();

            $table->string('status', 20)->default('draft'); // draft|pending_approval|pending_receipt|completed|cancelled

            $table->unsignedBigInteger('submitted_by_user_id')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('received_by_user_id')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->unsignedBigInteger('cancelled_by_user_id')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason', 255)->nullable();

            $table->timestamps();

            $table->foreign('from_subdepartment_id')->references('Subdepartment_ID')->on('tbl_subdepartment');
            $table->foreign('to_subdepartment_id')->references('Subdepartment_ID')->on('tbl_subdepartment');
            $table->foreign('created_by_user_id')->references('id')->on('users');
            $table->foreign('submitted_by_user_id')->references('id')->on('users');
            $table->foreign('approved_by_user_id')->references('id')->on('users');
            $table->foreign('received_by_user_id')->references('id')->on('users');
            $table->foreign('cancelled_by_user_id')->references('id')->on('users');
        });
    }
    public function down(): void { Schema::dropIfExists('tbl_store_transfers'); }
};