<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'tbl_department';

    protected $primaryKey = 'Department_ID';

    public $timestamps = false;

    protected $fillable = [
        'Department_Name',
        'Department_Location',
        'Branch_ID',
        'Department_Status',
        'status',
    ];
}
