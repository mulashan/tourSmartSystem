<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeJobCode extends Model
{
    protected $table = 'employee_job_codes';

    protected $primaryKey = 'employee_job_code_id';

    protected $fillable = [
        'job_code',
    ];
}
