<?php

namespace App\Models\Workshop;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;

class Mechanic extends Model
{
    protected $fillable = ['employee_id', 'name', 'specialization', 'hourly_rate', 'status'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'Employee_ID');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: ($this->employee->Employee_Name ?? 'Mechanic #' . $this->id);
    }
}
