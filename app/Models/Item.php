<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 'tbl_items';

    protected $fillable = [
        'product_name',
        'product_code_prefix',
        'product_code',
        'unit_of_measure_id',
        'item_category_id',
        'status',
        'reorder_level',
        'minimum_reorder_level',
        'maximum_reorder_level',
    ];

    public function itemCategory()
    {
        return $this->belongsTo(Lookup::class, 'item_category_id');
    }

    public function unitOfMeasure()
    {
        return $this->belongsTo(Lookup::class, 'unit_of_measure_id');
    }
}