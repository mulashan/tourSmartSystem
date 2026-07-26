<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreRequisitionItem extends Model
{
    protected $table = 'tbl_store_requisition_items';

    protected $fillable = ['store_requisition_id', 'item_id', 'units', 'items_per_unit', 'quantity', 'item_details','procurement_status', 'rejection_reason',];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}