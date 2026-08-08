<?php

namespace App\Models\Workshop;

use App\Models\Workshop\Concerns\TracksWorkshopUsers;
use Illuminate\Database\Eloquent\Model;

class JobMechanic extends Model
{
    use TracksWorkshopUsers;

    protected $table = 'job_mechanics';

    protected $fillable = ['job_card_id', 'mechanic_id', 'assigned_date', 'role', 'hours_worked', 'completion_percent', 'status'];

    protected $casts = ['assigned_date' => 'date'];

    public function mechanic()
    {
        return $this->belongsTo(Mechanic::class);
    }
}
