<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrnWithoutPoItem extends Model
{
    protected $table = 'tbl_grn_without_po_items';

    protected $fillable = ['grn_id', 'item_id', 'remarks'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function batches()
    {
        return $this->hasMany(GrnWithoutPoBatch::class, 'grn_item_id');
    }
}