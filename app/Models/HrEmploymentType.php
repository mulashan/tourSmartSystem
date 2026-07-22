<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrEmploymentType extends Model
{
    protected $table = 'hr_employment_types';

    protected $fillable = [
        'name',
        'branch_id',
    ];
}
