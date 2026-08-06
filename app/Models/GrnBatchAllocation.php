<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrnBatchAllocation extends Model
{
    protected $table = 'tbl_grn_batch_allocations';

    protected $fillable = ['grn_item_id', 'stock_batch_id', 'quantity_allocated'];

    public function stockBatch()
    {
        return $this->belongsTo(StockBatch::class);
    }
}