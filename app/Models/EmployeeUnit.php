<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeUnit extends Model
{
    protected $table = 'employee_units';

    protected $fillable = [
        'unit_name',
        'Department_ID',
        'Employee_ID',
    ];
}
