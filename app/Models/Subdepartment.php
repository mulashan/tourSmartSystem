<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subdepartment extends Model
{
    protected $table = 'tbl_subdepartment';

    protected $primaryKey = 'Subdepartment_ID';

    public $timestamps = false;

    protected $fillable = [
        'Subdepartment_Name',
        'Department_ID',
        'status',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'Department_ID', 'Department_ID');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'tbl_user_subdepartments', 'subdepartment_id', 'user_id', 'Subdepartment_ID', 'id');
    }
}