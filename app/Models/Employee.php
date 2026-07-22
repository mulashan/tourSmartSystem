<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'tbl_employee';

    protected $primaryKey = 'Employee_ID';

    public $timestamps = false;

    protected $fillable = [
        'Employee_Name',
        'Employee_Type_ID',
        'Employee_Type',
        'Employee_Number',
        'Employee_Check_Number',
        'Employee_Title',
        'Employee_Title_ID',
        'Employee_Job_Code',
        'Employee_Branch_Name',
        'Department_ID',
        'Account_Status',
        'employee_signature',
        'photo',
        'Phone_Number',
        'date_of_birth',
        'gender',
        'national_id',
        'physical_address',
        'created_at',
        'created_by',
        'Expire_Date',
        'Unit_ID',
        'Included_In_Payroll',
        'Employee_Status',
        'Msd_User',
        'gportalUser',
    ];
}
