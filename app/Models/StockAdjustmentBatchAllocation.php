<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustmentBatchAllocation extends Model
{
    protected $table = 'tbl_stock_adjustment_batch_allocations';
    protected $fillable = ['adjustment_item_id', 'stock_batch_id', 'quantity_allocated'];

    public function stockBatch() { return $this->belongsTo(StockBatch::class); }
}