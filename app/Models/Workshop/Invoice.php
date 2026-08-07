<?php

namespace App\Models\Workshop;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = ['job_card_id', 'invoice_no', 'labour_total', 'parts_total', 'tax', 'discount', 'other_charges', 'grand_total', 'status'];
}
