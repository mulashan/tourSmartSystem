<?php

namespace App\Models\Workshop;

use Illuminate\Database\Eloquent\Model;

class LabourEntry extends Model
{
    protected $fillable = ['job_card_id', 'mechanic_id', 'work_done', 'hours', 'rate', 'amount', 'date'];

    protected $casts = ['date' => 'date'];

    public function mechanic()
    {
        return $this->belongsTo(Mechanic::class);
    }
}
