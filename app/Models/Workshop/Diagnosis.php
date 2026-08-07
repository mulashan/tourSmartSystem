<?php

namespace App\Models\Workshop;

use App\Models\Workshop\Concerns\TracksWorkshopUsers;
use Illuminate\Database\Eloquent\Model;

class Diagnosis extends Model
{
    use TracksWorkshopUsers;

    protected $table = 'diagnosis';

    protected $fillable = [
        'job_card_id',
        'mechanic_id',
        'symptoms',
        'findings',
        'root_cause',
        'recommendation',
        'estimated_hours',
        'estimated_parts_cost',
        'approved',
    ];

    protected $casts = ['approved' => 'boolean'];

    public function mechanic()
    {
        return $this->belongsTo(Mechanic::class);
    }
}
