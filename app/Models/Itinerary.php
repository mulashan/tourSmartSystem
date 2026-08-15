<?php

namespace App\Models;

use App\Models\ItineraryLeg;
use Illuminate\Database\Eloquent\Model;

class Itinerary extends Model
{
    protected $table = 'tbl_itineraries';
    protected $fillable = [
        'trip_number', 'subdepartment_id', 'clients', 'start_point', 'destination', 'return_point',
        'start_date', 'end_date', 'comments', 'status', 'vehicle_id', 'driver_employee_id',
        'created_by_user_id', 'approved_by_user_id', 'approved_at', 'assigned_by_user_id', 'assigned_at',
        'cancelled_by_user_id', 'cancelled_at', 'cancel_reason', 'return_odometer', 'closed_by_user_id', 'closed_at',
    ];

    public function subdepartment() { return $this->belongsTo(Subdepartment::class, 'subdepartment_id', 'Subdepartment_ID'); }
    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function driver() { return $this->belongsTo(Employee::class, 'driver_employee_id', 'Employee_ID'); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function approvedBy() { return $this->belongsTo(User::class, 'approved_by_user_id'); }
    public function assignedBy() { return $this->belongsTo(User::class, 'assigned_by_user_id'); }
    public function cancelledBy() { return $this->belongsTo(User::class, 'cancelled_by_user_id'); }
    public function closedBy() { return $this->belongsTo(User::class, 'closed_by_user_id'); }
    public function legs() { return $this->hasMany(ItineraryLeg::class, 'itinerary_id')->orderBy('leg_number'); }
    public function fuelAssignments() { return $this->hasMany(ItineraryFuel::class, 'itinerary_id'); }
    public function gatePass() { return $this->hasOne(GatePass::class); }
}