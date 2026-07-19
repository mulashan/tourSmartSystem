<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
              $table->string('second_name')->after('name');
              $table->string('other_names')->after('second_name');
              $table->string('date_of_birth')->after('other_names');
              $table->string('gender')->after('date_of_birth');
              $table->integer('branch_id')->after('gender');
              $table->string('national_id')->nullable()->after('branch_id');
             $table->integer('privilege_id')->after('email');
            $table->text('physical_address')->nullable()->after('privilege_id');
            $table->string('photo')->nullable()->after('physical_address');
           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'second_name',
                'other_names',
                'date_of_birth',
                'gender',
                'branch_id',
                'national_id',
                'privilege_id',
                'physical_address',
                'photo',
                
            ]);
        });
    }
};
