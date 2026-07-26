<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrnItem extends Model
{
    protected $table = 'tbl_grn_items';

    protected $fillable = ['grn_id', 'lpo_item_id', 'item_id', 'remarks'];

    public function lpoItem()
    {
        return $this->belongsTo(LocalPurchaseOrderItem::class, 'lpo_item_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function batches()
    {
        return $this->hasMany(GrnBatch::class, 'grn_item_id');
    }
}