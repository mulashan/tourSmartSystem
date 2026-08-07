<?php

namespace App\Models\Workshop;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = ['customer_id', 'registration_no', 'make', 'model', 'color', 'vin'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function jobCards()
    {
        return $this->hasMany(JobCard::class);
    }
}
