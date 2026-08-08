<?php

namespace App\Models;

use App\Models\StockAdjustmentBatch;
use Illuminate\Database\Eloquent\Model;

class StockAdjustmentItem extends Model
{
    protected $table = 'tbl_stock_adjustment_items';
    protected $fillable = ['adjustment_id', 'item_id', 'quantity'];

    public function item() { return $this->belongsTo(Item::class); }
    public function batches() { return $this->hasMany(StockAdjustmentBatch::class, 'adjustment_item_id'); }
    public function allocations() { return $this->hasMany(StockAdjustmentBatchAllocation::class, 'adjustment_item_id'); }
    public function adjustment() { return $this->belongsTo(StockAdjustment::class, 'adjustment_id'); }
}