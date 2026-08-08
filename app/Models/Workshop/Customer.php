<?php

namespace App\Models\Workshop;

use App\Models\Workshop\Concerns\TracksWorkshopUsers;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use TracksWorkshopUsers;

    protected $fillable = ['name', 'phone', 'email', 'address'];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}
