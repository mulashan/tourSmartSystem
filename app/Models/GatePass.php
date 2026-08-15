<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GatePass extends Model
{
    protected $table = 'tbl_gate_passes';
    protected $fillable = [
        'gate_pass_no', 'itinerary_id', 'vehicle_id', 'driver_employee_id', 'date_time_out', 'expected_return',
        'odometer_reading', 'fuel_level', 'passengers', 'authorized_by_user_id', 'printed_at', 'created_by_user_id',
    ];

    public function itinerary() { return $this->belongsTo(Itinerary::class); }
    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function driver() { return $this->belongsTo(Employee::class, 'driver_employee_id', 'Employee_ID'); }
    public function authorizedBy() { return $this->belongsTo(User::class, 'authorized_by_user_id'); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by_user_id'); }
}