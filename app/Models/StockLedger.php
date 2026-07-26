<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLedger extends Model
{
    protected $table = 'tbl_stock_ledger';

    protected $fillable = [
        'item_id', 'subdepartment_id', 'movement_type', 'reference_type', 'reference_id',
        'quantity_in', 'quantity_out', 'balance_after', 'grn_batch_id', 'created_by_user_id', 'moved_at',
    ];
}