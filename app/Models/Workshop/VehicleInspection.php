<?php

namespace App\Models\Workshop;

use Illuminate\Database\Eloquent\Model;

class VehicleInspection extends Model
{
    protected $fillable = [
        'job_card_id',
        'inspection_date',
        'inspector_name',
        'fuel_level',
        'tyre_condition',
        'battery_condition',
        'fluid_status',
        'visible_damages',
        'safety_checklist',
        'initial_recommendation',
        'remarks',
    ];

    protected $casts = ['inspection_date' => 'date'];

    public function jobCard()
    {
        return $this->belongsTo(JobCard::class);
    }
}
