<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItineraryLeg extends Model
{
    protected $table = 'tbl_itinerary_legs';
    protected $fillable = ['itinerary_id', 'leg_number', 'start_point', 'destination', 'leg_date', 'notes'];

    public function itinerary() { return $this->belongsTo(Itinerary::class, 'itinerary_id'); }
    public function fuel() { return $this->hasOne(ItineraryFuel::class, 'leg_id'); }
}