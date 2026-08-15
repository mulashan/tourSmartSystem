<?php
// database/migrations/2026_08_17_000000_create_tbl_vehicles_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_vehicles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('vehicle_code', 30)->unique();
            $table->string('registration_no', 50)->unique();
            $table->string('vehicle_type', 100)->nullable();
            $table->string('make', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('chassis_no', 100)->nullable();
            $table->string('engine_no', 100)->nullable();
            $table->string('color', 50)->nullable();
            $table->unsignedInteger('seating_capacity')->nullable();
            $table->string('fuel_type', 50)->nullable();
            $table->unsignedInteger('ownership_type_id')->nullable();
            $table->string('owner', 150)->nullable();
            $table->unsignedInteger('current_location_id')->nullable();
            $table->unsignedInteger('current_odometer')->default(0);
            $table->string('status', 30)->default('available');
            $table->unsignedInteger('assigned_driver_employee_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('subdepartment_id');
            $table->unsignedBigInteger('created_by_user_id');
            $table->timestamps();

            $table->foreign('ownership_type_id')->references('id')->on('tbl_lookups');
            $table->foreign('current_location_id')->references('id')->on('tbl_lookups');
            $table->foreign('assigned_driver_employee_id')->references('Employee_ID')->on('tbl_employee');
            $table->foreign('subdepartment_id')->references('Subdepartment_ID')->on('tbl_subdepartment');
            $table->foreign('created_by_user_id')->references('id')->on('users');
        });

        Schema::create('tbl_vehicle_rental_agreements', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('vehicle_id');
            $table->string('owner', 150);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('agreement_document', 255)->nullable();
            $table->string('contact_info', 255)->nullable();
            $table->unsignedBigInteger('created_by_user_id');
            $table->timestamps();

            $table->foreign('vehicle_id')->references('id')->on('tbl_vehicles')->cascadeOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users');
        });

        Schema::create('tbl_vehicle_driver_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('vehicle_id');
            $table->unsignedInteger('employee_id')->nullable();
            $table->unsignedBigInteger('assigned_by_user_id');
            $table->timestamp('assigned_at');
            $table->timestamp('unassigned_at')->nullable();
            $table->timestamps();

            $table->foreign('vehicle_id')->references('id')->on('tbl_vehicles')->cascadeOnDelete();
            $table->foreign('employee_id')->references('Employee_ID')->on('tbl_employee');
            $table->foreign('assigned_by_user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_vehicle_driver_history');
        Schema::dropIfExists('tbl_vehicle_rental_agreements');
        Schema::dropIfExists('tbl_vehicles');
    }
};