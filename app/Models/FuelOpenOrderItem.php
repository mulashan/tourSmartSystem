<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuelOpenOrderItem extends Model
{
    protected $table = 'tbl_fuel_open_order_items';
    protected $fillable = ['open_order_id', 'vehicle_id', 'driver_employee_id', 'fuel_type', 'quantity', 'unit_price', 'total_amount', 'odometer_reading', 'recorded_by_user_id', 'recorded_at'];

    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function driver() { return $this->belongsTo(Employee::class, 'driver_employee_id', 'Employee_ID'); }
    public function recordedBy() { return $this->belongsTo(User::class, 'recorded_by_user_id'); }
    public function openOrder() { return $this->belongsTo(FuelOpenOrder::class, 'open_order_id'); }
}