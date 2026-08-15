<?php
// database/migrations/2026_08_18_000000_create_tbl_vehicle_insurance_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_vehicle_insurance', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('vehicle_id');
            $table->string('insurance_company', 150);
            $table->string('policy_number', 100);
            $table->unsignedInteger('insurance_type_id');
            $table->date('start_date');
            $table->date('expire_date');
            $table->decimal('premium', 12, 2)->nullable();
            $table->string('contact', 255)->nullable();
            $table->string('certificate_document', 255)->nullable();
            $table->string('status', 20)->default('active'); // active | expired | cancelled
            $table->unsignedBigInteger('created_by_user_id');
            $table->timestamps();

            $table->foreign('vehicle_id')->references('id')->on('tbl_vehicles')->cascadeOnDelete();
            $table->foreign('insurance_type_id')->references('id')->on('tbl_lookups');
            $table->foreign('created_by_user_id')->references('id')->on('users');
        });

        Schema::create('tbl_vehicle_insurance_coverages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('insurance_id');
            $table->unsignedInteger('coverage_id');

            $table->foreign('insurance_id')->references('id')->on('tbl_vehicle_insurance')->cascadeOnDelete();
            $table->foreign('coverage_id')->references('id')->on('tbl_lookups');
            $table->unique(['insurance_id', 'coverage_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_vehicle_insurance_coverages');
        Schema::dropIfExists('tbl_vehicle_insurance');
    }
};