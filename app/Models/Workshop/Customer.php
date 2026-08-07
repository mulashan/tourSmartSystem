<?php

namespace App\Models\Workshop;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'address'];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}
