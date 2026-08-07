<?php

namespace App\Models\Workshop;

use App\Models\Item;
use App\Models\Subdepartment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PartUsed extends Model
{
    protected $table = 'parts_used';

    protected $fillable = ['job_card_id', 'part_id', 'quantity', 'unit_price', 'total', 'issued_by', 'issue_date', 'subdepartment_id'];

    protected $casts = ['issue_date' => 'date'];

    public function part()
    {
        return $this->belongsTo(Item::class, 'part_id');
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function subdepartment()
    {
        return $this->belongsTo(Subdepartment::class, 'subdepartment_id', 'Subdepartment_ID');
    }
}
