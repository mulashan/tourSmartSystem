<?php
// database/migrations/2026_08_19_000000_create_tbl_maintenance_orders_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_maintenance_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('vehicle_id');
            $table->unsignedInteger('driver_employee_id')->nullable();
            $table->text('problem');
            $table->unsignedInteger('workshop_subdepartment_id');
            $table->unsignedInteger('odometer_at_order')->nullable();
            $table->string('status', 20)->default('open'); // open | completed | cancelled
            $table->unsignedBigInteger('created_by_user_id');
            $table->unsignedBigInteger('completed_by_user_id')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('completion_notes', 255)->nullable();
            $table->timestamps();

            $table->foreign('vehicle_id')->references('id')->on('tbl_vehicles');
            $table->foreign('driver_employee_id')->references('Employee_ID')->on('tbl_employee');
            $table->foreign('workshop_subdepartment_id')->references('Subdepartment_ID')->on('tbl_subdepartment');
            $table->foreign('created_by_user_id')->references('id')->on('users');
            $table->foreign('completed_by_user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_maintenance_orders');
    }
};