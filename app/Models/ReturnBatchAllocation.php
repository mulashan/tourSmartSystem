<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnBatchAllocation extends Model
{
    protected $table = 'tbl_return_batch_allocations';

    protected $fillable = ['return_item_id', 'stock_batch_id', 'quantity_allocated'];

    public function stockBatch()
    {
        return $this->belongsTo(StockBatch::class);
    }
}