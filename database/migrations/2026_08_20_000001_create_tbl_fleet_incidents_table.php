<?php
// database/migrations/2026_08_20_000001_create_tbl_fleet_incidents_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_fleet_incidents', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type', 20); // accident | road_fine
            $table->unsignedInteger('vehicle_id');
            $table->unsignedInteger('driver_employee_id')->nullable();
            $table->unsignedInteger('itinerary_id')->nullable();
            $table->date('incident_date');
            $table->time('incident_time')->nullable();
            $table->string('location', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('police_report', 100)->nullable();
            $table->text('injuries')->nullable();
            $table->text('damages')->nullable();
            $table->string('covered_by', 20)->nullable(); // company | insurance | driver
            $table->decimal('estimated_cost', 12, 2)->nullable();
            $table->decimal('actual_cost', 12, 2)->nullable();
            $table->string('status', 20)->default('open'); // open | closed
            $table->unsignedInteger('subdepartment_id');
            $table->unsignedBigInteger('created_by_user_id');
            $table->timestamps();

            $table->foreign('vehicle_id')->references('id')->on('tbl_vehicles');
            $table->foreign('driver_employee_id')->references('Employee_ID')->on('tbl_employee');
            $table->foreign('itinerary_id')->references('id')->on('tbl_itineraries');
            $table->foreign('subdepartment_id')->references('Subdepartment_ID')->on('tbl_subdepartment');
            $table->foreign('created_by_user_id')->references('id')->on('users');
        });

        Schema::create('tbl_fleet_incident_photos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('incident_id');
            $table->string('path', 255);
            $table->timestamps();

            $table->foreign('incident_id')->references('id')->on('tbl_fleet_incidents')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_fleet_incident_photos');
        Schema::dropIfExists('tbl_fleet_incidents');
    }
};