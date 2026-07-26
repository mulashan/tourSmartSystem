<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrnPurchaseOrder extends Model
{
    protected $table = 'tbl_grn_purchase_order';

    protected $primaryKey = 'Grn_Purchase_Order_ID';

    protected $fillable = [
        'local_purchase_order_id', 'supplier_id', 'created_by_user_id', 'Sub_Department_ID',
        'Purchase_Description', 'Delivery_Note_Number', 'Delivery_Note_Attachment',
        'Invoice_Number', 'Invoice_Attachment', 'Delivery_Date', 'Delivery_Person',
        'status', 'submitted_by_user_id', 'submitted_at', 'approved_by_user_id', 'approved_at',
    ];

    public function localPurchaseOrder()
    {
        return $this->belongsTo(LocalPurchaseOrder::class, 'local_purchase_order_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function subdepartment()
    {
        return $this->belongsTo(Subdepartment::class, 'Sub_Department_ID', 'Subdepartment_ID');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function items()
    {
        return $this->hasMany(GrnItem::class, 'grn_id');
    }
}