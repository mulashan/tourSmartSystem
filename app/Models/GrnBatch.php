<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrnBatch extends Model
{
    protected $table = 'tbl_grn_batches';
    protected $casts = [
        'manufacture_date' => 'date',
        'expiry_date' => 'date',
        'received_date' => 'date',
    ];

    protected $fillable = [
        'grn_item_id', 'batch_number', 'units', 'items_per_unit', 'quantity',
        'buying_price', 'manufacture_date', 'expiry_date', 'received_date',
    ];
}