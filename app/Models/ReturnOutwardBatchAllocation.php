<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnOutwardBatchAllocation extends Model
{
    protected $table = 'tbl_return_outward_batch_allocations';
    protected $fillable = ['return_item_id', 'stock_batch_id', 'quantity_allocated'];

    public function stockBatch() { return $this->belongsTo(StockBatch::class); }
}