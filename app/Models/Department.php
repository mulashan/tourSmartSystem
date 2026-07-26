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
        'department_nature_id',
        'Department_Status',
        'status',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'Branch_ID', 'Branch_ID');
    }

    public function departmentNature()
    {
        return $this->belongsTo(DepartmentNature::class, 'department_nature_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_subdepartments', 'subdepartment_id', 'user_id', 'Subdepartment_ID', 'id');
    }
}
