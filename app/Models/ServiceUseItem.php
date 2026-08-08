<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceUseItem extends Model
{
    protected $table = 'tbl_service_use_items';
    protected $fillable = ['service_use_id', 'item_id', 'quantity'];

    public function item() { return $this->belongsTo(Item::class); }
}