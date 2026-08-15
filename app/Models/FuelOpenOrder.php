<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuelOpenOrder extends Model
{
    protected $table = 'tbl_fuel_open_orders';
    protected $fillable = ['subdepartment_id', 'fuel_source_id', 'status', 'opened_by_user_id', 'opened_at', 'closed_by_user_id', 'closed_at'];

    public function fuelSource() { return $this->belongsTo(Lookup::class, 'fuel_source_id'); }
    public function openedBy() { return $this->belongsTo(User::class, 'opened_by_user_id'); }
    public function closedBy() { return $this->belongsTo(User::class, 'closed_by_user_id'); }
    public function items() { return $this->hasMany(FuelOpenOrderItem::class, 'open_order_id'); }
}