<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceOrder extends Model
{
    protected $table = 'tbl_maintenance_orders';
    protected $fillable = [
        'vehicle_id', 'driver_employee_id', 'problem', 'workshop_subdepartment_id', 'odometer_at_order',
        'status', 'created_by_user_id', 'completed_by_user_id', 'completed_at', 'completion_notes',
    ];

    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function driver() { return $this->belongsTo(Employee::class, 'driver_employee_id', 'Employee_ID'); }
    public function workshop() { return $this->belongsTo(Subdepartment::class, 'workshop_subdepartment_id', 'Subdepartment_ID'); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function completedBy() { return $this->belongsTo(User::class, 'completed_by_user_id'); }
}