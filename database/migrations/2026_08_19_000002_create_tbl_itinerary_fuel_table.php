<?php
// database/migrations/2026_08_19_000002_create_tbl_itinerary_fuel_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_itinerary_fuel', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('itinerary_id');
            $table->unsignedInteger('leg_id')->nullable();
            $table->unsignedInteger('vehicle_id');
            $table->unsignedInteger('driver_employee_id')->nullable();
            $table->unsignedInteger('fuel_source_id');
            $table->string('fuel_type', 50);
            $table->decimal('quantity_assigned', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_amount', 12, 2);
            $table->unsignedInteger('odometer_reading')->nullable();
            $table->string('remarks', 255)->nullable();

            $table->string('status', 20)->default('assigned'); // assigned | issued | cancelled
            $table->unsignedBigInteger('assigned_by_user_id');
            $table->timestamp('assigned_at');
            $table->decimal('issued_quantity', 10, 2)->nullable();
            $table->unsignedBigInteger('issued_by_user_id')->nullable();
            $table->timestamp('issued_at')->nullable();

            $table->timestamps();

            $table->foreign('itinerary_id')->references('id')->on('tbl_itineraries');
            $table->foreign('leg_id')->references('id')->on('tbl_itinerary_legs');
            $table->foreign('vehicle_id')->references('id')->on('tbl_vehicles');
            $table->foreign('driver_employee_id')->references('Employee_ID')->on('tbl_employee');
            $table->foreign('fuel_source_id')->references('id')->on('tbl_lookups');
            $table->foreign('assigned_by_user_id')->references('id')->on('users');
            $table->foreign('issued_by_user_id')->references('id')->on('users');
        });

        Schema::create('tbl_fuel_open_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('subdepartment_id');
            $table->unsignedInteger('fuel_source_id');
            $table->string('status', 20)->default('open'); // open | closed
            $table->unsignedBigInteger('opened_by_user_id');
            $table->timestamp('opened_at');
            $table->unsignedBigInteger('closed_by_user_id')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->foreign('subdepartment_id')->references('Subdepartment_ID')->on('tbl_subdepartment');
            $table->foreign('fuel_source_id')->references('id')->on('tbl_lookups');
            $table->foreign('opened_by_user_id')->references('id')->on('users');
            $table->foreign('closed_by_user_id')->references('id')->on('users');
        });

        Schema::create('tbl_fuel_open_order_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('open_order_id');
            $table->unsignedInteger('vehicle_id');
            $table->unsignedInteger('driver_employee_id')->nullable();
            $table->string('fuel_type', 50);
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_amount', 12, 2);
            $table->unsignedInteger('odometer_reading')->nullable();
            $table->unsignedBigInteger('recorded_by_user_id');
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->foreign('open_order_id')->references('id')->on('tbl_fuel_open_orders')->cascadeOnDelete();
            $table->foreign('vehicle_id')->references('id')->on('tbl_vehicles');
            $table->foreign('driver_employee_id')->references('Employee_ID')->on('tbl_employee');
            $table->foreign('recorded_by_user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_fuel_open_order_items');
        Schema::dropIfExists('tbl_fuel_open_orders');
        Schema::dropIfExists('tbl_itinerary_fuel');
    }
};