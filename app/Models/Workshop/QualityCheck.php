<?php

namespace App\Models\Workshop;

use Illuminate\Database\Eloquent\Model;

class QualityCheck extends Model
{
    protected $fillable = [
        'job_card_id',
        'inspector_id',
        'inspection_date',
        'repair_completed',
        'road_test',
        'no_oil_leaks',
        'brakes_checked',
        'lights_working',
        'complaint_resolved',
        'remarks',
        'status',
    ];

    protected $casts = [
        'inspection_date' => 'date',
        'repair_completed' => 'boolean',
        'road_test' => 'boolean',
        'no_oil_leaks' => 'boolean',
        'brakes_checked' => 'boolean',
        'lights_working' => 'boolean',
        'complaint_resolved' => 'boolean',
    ];
}
