<?php

namespace App\Models\Workshop;

use Illuminate\Database\Eloquent\Model;

class JobCompletion extends Model
{
    protected $table = 'job_completion';

    protected $fillable = ['job_card_id', 'completion_notes', 'completed_by', 'completed_date', 'vehicle_tested', 'ready_for_inspection'];

    protected $casts = ['completed_date' => 'date', 'vehicle_tested' => 'boolean', 'ready_for_inspection' => 'boolean'];
}
