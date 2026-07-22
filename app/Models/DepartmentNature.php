<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentNature extends Model
{
    protected $table = 'tbl_department_nature';

    protected $fillable = [
        'department_nature',
    ];
}
