<?php
// database/migrations/2026_08_20_000000_create_tbl_gate_passes_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_gate_passes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('gate_pass_no', 30)->unique();
            $table->unsignedInteger('itinerary_id')->unique();
            $table->unsignedInteger('vehicle_id');
            $table->unsignedInteger('driver_employee_id')->nullable();
            $table->dateTime('date_time_out');
            $table->dateTime('expected_return')->nullable();
            $table->unsignedInteger('odometer_reading')->nullable();
            $table->string('fuel_level', 30)->nullable();
            $table->string('passengers', 500)->nullable();
            $table->unsignedBigInteger('authorized_by_user_id');
            $table->timestamp('printed_at')->nullable();
            $table->unsignedBigInteger('created_by_user_id');
            $table->timestamps();

            $table->foreign('itinerary_id')->references('id')->on('tbl_itineraries');
            $table->foreign('vehicle_id')->references('id')->on('tbl_vehicles');
            $table->foreign('driver_employee_id')->references('Employee_ID')->on('tbl_employee');
            $table->foreign('authorized_by_user_id')->references('id')->on('users');
            $table->foreign('created_by_user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_gate_passes');
    }
};