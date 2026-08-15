<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItineraryFuel extends Model
{
    protected $table = 'tbl_itinerary_fuel';
    protected $fillable = [
        'itinerary_id', 'leg_id', 'vehicle_id', 'driver_employee_id', 'fuel_source_id', 'fuel_type',
        'quantity_assigned', 'unit_price', 'total_amount', 'odometer_reading', 'remarks', 'status',
        'assigned_by_user_id', 'assigned_at', 'issued_quantity', 'issued_by_user_id', 'issued_at',
    ];

    public function itinerary() { return $this->belongsTo(Itinerary::class); }
    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function driver() { return $this->belongsTo(Employee::class, 'driver_employee_id', 'Employee_ID'); }
    public function fuelSource() { return $this->belongsTo(Lookup::class, 'fuel_source_id'); }
    public function assignedBy() { return $this->belongsTo(User::class, 'assigned_by_user_id'); }
    public function issuedBy() { return $this->belongsTo(User::class, 'issued_by_user_id'); }
    public function leg() { return $this->belongsTo(ItineraryLeg::class, 'leg_id'); }
}