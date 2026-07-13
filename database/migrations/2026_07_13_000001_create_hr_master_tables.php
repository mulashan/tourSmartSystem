<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
public function up(): void {

Schema::create('tbl_company', function(Blueprint $table){
 $table->increments('Company_ID');
 $table->string('Company_Name',300)->unique();
 $table->string('status')->nullable();
});

Schema::create('tbl_branches', function(Blueprint $table){
 $table->increments('Branch_ID');
 $table->string('Branch_Name',300)->unique();
 $table->string('token')->nullable();
 $table->dateTime('token_date')->nullable();
 $table->string('BannerLink',200)->nullable();
 $table->unsignedInteger('Company_ID')->nullable();
 $table->foreign('Company_ID')->references('Company_ID')->on('tbl_company')->cascadeOnUpdate()->cascadeOnDelete();
});

Schema::create('tbl_department', function(Blueprint $table){
 $table->increments('Department_ID');
 $table->string('Department_Name',100)->unique();
 $table->string('Department_Location',100);
 $table->unsignedInteger('Branch_ID');
 $table->string('Department_Status',15)->default('active');
 $table->integer('status')->default(0);
 $table->foreign('Branch_ID')->references('Branch_ID')->on('tbl_branches');
});

Schema::create('employee_job_codes',function(Blueprint $table){
 $table->increments('employee_job_code_id');
 $table->string('job_code')->unique();
 $table->timestamps();
});

Schema::create('hr_employment_types',function(Blueprint $table){
 $table->increments('id');
 $table->string('name');
 $table->unsignedInteger('branch_id')->nullable();
 $table->timestamps();
 $table->foreign('branch_id')->references('Branch_ID')->on('tbl_branches');
});

Schema::create('tbl_department_nature',function(Blueprint $table){
 $table->increments('id');
 $table->string('department_nature');
 $table->timestamps();
});

Schema::create('job_titles',function(Blueprint $table){
 $table->increments('id');
 $table->string('title')->nullable();
 $table->string('title_location')->default('others');
 $table->timestamps();
});

Schema::create('tbl_designation',function(Blueprint $table){
 $table->increments('designation_ID');
 $table->string('designation',30);
 $table->integer('session_time_limit')->default(30);
});

Schema::create('employee_units',function(Blueprint $table){
 $table->bigIncrements('id');
 $table->string('unit_name');
 $table->unsignedInteger('Department_ID');
 $table->unsignedInteger('Employee_ID')->nullable();
 $table->timestamps();
 $table->foreign('Department_ID')->references('Department_ID')->on('tbl_department')->restrictOnDelete()->cascadeOnUpdate();
});

Schema::create('tbl_employee',function(Blueprint $table){
 $table->increments('Employee_ID');
 $table->string('Employee_Name',300);
 $table->unsignedInteger('Employee_Type_ID')->nullable();
 $table->string('Employee_Type',300);
 $table->string('Employee_Number',150)->unique();
 $table->string('Employee_Check_Number',19);
 $table->string('Employee_Title',150);
 $table->unsignedInteger('Employee_Title_ID')->nullable();
 $table->string('Employee_Job_Code',150);
 $table->string('Employee_Branch_Name',150);
 $table->unsignedInteger('Department_ID')->nullable();
 $table->string('Account_Status',40)->default('active');
 $table->longText('employee_signature')->nullable();
 $table->string('Phone_Number',50);
 $table->dateTime('created_at')->nullable();
 $table->unsignedInteger('created_by')->nullable();
 $table->date('Expire_Date')->nullable();
 $table->unsignedBigInteger('Unit_ID')->nullable();
 $table->string('Included_In_Payroll')->default('no');
 $table->string('Employee_Status')->default('At Work');
 $table->string('Msd_User')->default('no');
 $table->string('gportalUser')->default('no');
 $table->index(['Department_ID','Employee_Number']);
 $table->foreign('Department_ID')->references('Department_ID')->on('tbl_department');
 $table->foreign('Employee_Title_ID')->references('id')->on('job_titles');
 $table->foreign('Employee_Type_ID')->references('designation_ID')->on('tbl_designation');
});

Schema::table('employee_units',function(Blueprint $table){
 $table->foreign('Employee_ID')->references('Employee_ID')->on('tbl_employee')->restrictOnDelete()->cascadeOnUpdate();
});
Schema::table('tbl_employee',function(Blueprint $table){
 $table->foreign('Unit_ID')->references('id')->on('employee_units')->restrictOnDelete()->cascadeOnUpdate();
});
}
public function down(): void {
Schema::dropIfExists('tbl_employee');
Schema::dropIfExists('employee_units');
Schema::dropIfExists('tbl_designation');
Schema::dropIfExists('job_titles');
Schema::dropIfExists('tbl_department_nature');
Schema::dropIfExists('hr_employment_types');
Schema::dropIfExists('employee_job_codes');
Schema::dropIfExists('tbl_department');
Schema::dropIfExists('tbl_branches');
Schema::dropIfExists('tbl_company');
}
};
