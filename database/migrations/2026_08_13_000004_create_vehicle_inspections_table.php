<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vehicle_inspections')) {
            return;
        }

        Schema::create('vehicle_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_card_id')->constrained('job_cards')->cascadeOnDelete();
            $table->date('inspection_date');
            $table->string('inspector_name')->nullable();
            $table->string('fuel_level', 40)->nullable();
            $table->string('tyre_condition')->nullable();
            $table->string('battery_condition')->nullable();
            $table->string('fluid_status')->nullable();
            $table->text('visible_damages')->nullable();
            $table->text('safety_checklist')->nullable();
            $table->text('initial_recommendation')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_inspections');
    }
};
