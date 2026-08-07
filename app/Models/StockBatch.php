<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockBatch extends Model
{
    protected $table = 'tbl_stock_batches';

    protected $fillable = [
        'item_id', 'subdepartment_id', 'batch_number', 'manufacture_date', 'expiry_date',
        'buying_price', 'quantity_received', 'quantity_remaining', 'source_type', 'source_id', 'received_date',
    ];

    protected $casts = [
        'manufacture_date' => 'date',
        'expiry_date' => 'date',
        'received_date' => 'date',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    // FEFO ordering: oldest expiry first, only batches with something left.
    public function scopeAvailableFefo($query, int $itemId, int $subdepartmentId)
    {
        return $query->where('item_id', $itemId)
            ->where('subdepartment_id', $subdepartmentId)
            ->where('quantity_remaining', '>', 0)
            ->orderBy('expiry_date');
    }
}