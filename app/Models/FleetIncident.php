<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FleetIncident extends Model
{
    protected $table = 'tbl_fleet_incidents';
    protected $fillable = [
        'type', 'vehicle_id', 'driver_employee_id', 'itinerary_id', 'incident_date', 'incident_time', 'location',
        'description', 'police_report', 'injuries', 'damages', 'covered_by', 'estimated_cost', 'actual_cost',
        'status', 'subdepartment_id', 'created_by_user_id',
    ];

    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function driver() { return $this->belongsTo(Employee::class, 'driver_employee_id', 'Employee_ID'); }
    public function itinerary() { return $this->belongsTo(Itinerary::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function photos() { return $this->hasMany(FleetIncidentPhoto::class, 'incident_id'); }
}