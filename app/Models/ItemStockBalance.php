<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemStockBalance extends Model
{
    protected $table = 'tbl_item_stock_balances';

    protected $fillable = ['item_id', 'subdepartment_id', 'quantity_balance'];
}