<?php

namespace App\Models;

use App\Models\ReturnBatchAllocation;
use Illuminate\Database\Eloquent\Model;

class ReturnItem extends Model
{
    protected $table = 'tbl_return_items';

    protected $fillable = ['return_id', 'item_id', 'quantity'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function allocations()
    {
        return $this->hasMany(ReturnBatchAllocation::class, 'return_item_id');
    }
}