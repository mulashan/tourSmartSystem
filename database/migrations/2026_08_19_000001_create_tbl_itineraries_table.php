<?php
// database/migrations/2026_08_19_000001_create_tbl_itineraries_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_itineraries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('trip_number', 30)->unique();
            $table->unsignedInteger('subdepartment_id');
            $table->string('clients', 500);
            $table->string('start_point', 150)->default('Arusha Office');
            $table->string('destination', 150);
            $table->string('return_point', 150)->default('Arusha Office');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('comments')->nullable();

            $table->string('status', 20)->default('pending'); // pending|approved|assigned|ready|in_progress|completed|cancelled|closed

            $table->unsignedInteger('vehicle_id')->nullable();
            $table->unsignedInteger('driver_employee_id')->nullable();

            $table->unsignedBigInteger('created_by_user_id');
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('assigned_by_user_id')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->unsignedBigInteger('cancelled_by_user_id')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason', 255)->nullable();
            $table->unsignedInteger('return_odometer')->nullable();
            $table->unsignedBigInteger('closed_by_user_id')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();

            $table->foreign('subdepartment_id')->references('Subdepartment_ID')->on('tbl_subdepartment');
            $table->foreign('vehicle_id')->references('id')->on('tbl_vehicles');
            $table->foreign('driver_employee_id')->references('Employee_ID')->on('tbl_employee');
            $table->foreign('created_by_user_id')->references('id')->on('users');
            $table->foreign('approved_by_user_id')->references('id')->on('users');
            $table->foreign('assigned_by_user_id')->references('id')->on('users');
            $table->foreign('cancelled_by_user_id')->references('id')->on('users');
            $table->foreign('closed_by_user_id')->references('id')->on('users');
        });

        Schema::create('tbl_itinerary_legs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('itinerary_id');
            $table->unsignedInteger('leg_number');
            $table->string('start_point', 150);
            $table->string('destination', 150);
            $table->date('leg_date')->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->foreign('itinerary_id')->references('id')->on('tbl_itineraries')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_itinerary_legs');
        Schema::dropIfExists('tbl_itineraries');
    }
};