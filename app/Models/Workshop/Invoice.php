<?php

namespace App\Models\Workshop;

use App\Models\Workshop\Concerns\TracksWorkshopUsers;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use TracksWorkshopUsers;

    protected $fillable = ['job_card_id', 'invoice_no', 'labour_total', 'parts_total', 'tax', 'discount', 'other_charges', 'grand_total', 'status'];
}
