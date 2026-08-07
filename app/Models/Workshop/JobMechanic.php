<?php

namespace App\Models\Workshop;

use Illuminate\Database\Eloquent\Model;

class JobMechanic extends Model
{
    protected $table = 'job_mechanics';

    protected $fillable = ['job_card_id', 'mechanic_id', 'assigned_date', 'role', 'hours_worked', 'completion_percent', 'status'];

    protected $casts = ['assigned_date' => 'date'];

    public function mechanic()
    {
        return $this->belongsTo(Mechanic::class);
    }
}
