<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequisitionItem extends Model
{
    protected $table = 'tbl_requisition_items';

    protected $fillable = ['requisition_id', 'item_id', 'quantity_requested','item_details'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}