<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocalPurchaseOrderItem extends Model
{
    protected $table = 'tbl_local_purchase_order_items';

    protected $primaryKey = 'lpo_item_id';

    protected $fillable = [
        'local_purchase_order_id', 'Quantity_Required', 'Containers_Required', 'Items_Per_Container_Required',
        'Price', 'Remark', 'Item_ID', 'Item_Status', 'Remain_Balance',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'Item_ID');
    }

    public function lpo() { return $this->belongsTo(LocalPurchaseOrder::class, 'local_purchase_order_id'); }
}