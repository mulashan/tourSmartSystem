<?php

namespace App\Models\Workshop;

use App\Models\Workshop\Concerns\TracksWorkshopUsers;
use Illuminate\Database\Eloquent\Model;

class RepairOrder extends Model
{
    use TracksWorkshopUsers;

    protected $fillable = ['job_card_id', 'repair_type', 'description', 'estimated_hours', 'estimated_cost', 'status'];

    public function jobCard()
    {
        return $this->belongsTo(JobCard::class);
    }
}
