<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrnOpenBalanceItem extends Model
{
    protected $table = 'tbl_grn_open_balance_items';

    protected $fillable = ['grn_id', 'item_id', 'remarks'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function batches()
    {
        return $this->hasMany(GrnOpenBalanceBatch::class, 'grn_item_id');
    }
}