<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleDriverHistory extends Model
{
    protected $table = 'tbl_vehicle_driver_history';
    protected $fillable = ['vehicle_id', 'employee_id', 'assigned_by_user_id', 'assigned_at', 'unassigned_at'];

    public function employee() { return $this->belongsTo(Employee::class, 'employee_id', 'Employee_ID'); }
    public function assignedBy() { return $this->belongsTo(User::class, 'assigned_by_user_id'); }
}