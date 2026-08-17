<?php

namespace App\Models;

use App\Models\VehicleRentalAgreement;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $table = 'tbl_vehicles';

    protected $fillable = [
        'vehicle_code', 'registration_no', 'vehicle_type', 'make', 'model', 'year', 'chassis_no', 'engine_no',
        'color', 'seating_capacity', 'fuel_type', 'ownership_type_id', 'owner', 'current_location_id',
        'current_odometer', 'status', 'assigned_driver_employee_id', 'is_active', 'subdepartment_id', 'created_by_user_id','purchase_odometer',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function ownershipType() { return $this->belongsTo(Lookup::class, 'ownership_type_id'); }
    public function currentLocation() { return $this->belongsTo(Lookup::class, 'current_location_id'); }
    public function assignedDriver() { return $this->belongsTo(Employee::class, 'assigned_driver_employee_id', 'Employee_ID'); }
    public function subdepartment() { return $this->belongsTo(Subdepartment::class, 'subdepartment_id', 'Subdepartment_ID'); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function rentalAgreements() { return $this->hasMany(VehicleRentalAgreement::class, 'vehicle_id')->orderByDesc('start_date'); }
    public function driverHistory() { return $this->hasMany(VehicleDriverHistory::class, 'vehicle_id')->orderByDesc('assigned_at'); }
    public function insurances() { return $this->hasMany(VehicleInsurance::class); }
    public function itineraries() { return $this->hasMany(Itinerary::class); }
}