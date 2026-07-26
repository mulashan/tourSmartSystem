<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_suppliers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('supplier_name', 150);
            $table->string('supplier_address', 255);
            $table->string('postal_address', 255);
            $table->string('contact_person_name', 150);
            $table->string('contact_person_mobile', 30);
            $table->string('contact_person_email', 150);
            $table->string('telephone', 30)->nullable();
            $table->string('fax', 30)->nullable();
            $table->string('physical_address', 255)->nullable();
            $table->timestamps();

            $table->unique('supplier_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_suppliers');
    }
};