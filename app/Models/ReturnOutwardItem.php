<?php

namespace App\Models;

use App\Models\ReturnOutwardBatchAllocation;
use Illuminate\Database\Eloquent\Model;

class ReturnOutwardItem extends Model
{
    protected $table = 'tbl_return_outward_items';
    protected $fillable = ['return_id', 'item_id', 'quantity'];

    public function item() { return $this->belongsTo(Item::class); }
    public function allocations() { return $this->hasMany(ReturnOutwardBatchAllocation::class, 'return_item_id'); }
}