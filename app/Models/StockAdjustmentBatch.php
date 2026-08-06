<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustmentBatch extends Model
{
    protected $table = 'tbl_stock_adjustment_batches';
    protected $fillable = ['adjustment_item_id', 'batch_number', 'units', 'items_per_unit', 'quantity', 'buying_price', 'manufacture_date', 'expiry_date', 'received_date'];
    protected $casts = ['manufacture_date' => 'date', 'expiry_date' => 'date', 'received_date' => 'date'];
}