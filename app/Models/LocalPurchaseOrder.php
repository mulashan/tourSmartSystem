<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocalPurchaseOrder extends Model
{
    protected $table = 'tbl_local_purchase_order';

    protected $primaryKey = 'local_purchase_order_id';

    protected $fillable = [
        'store_requisition_id', 'supplier_id', 'created_by_user_id', 'procurement_subdepartment_id',
        'currency_type', 'requisition_description', 'order_date', 'status', 'rejection_reason',
        'vat_charges', 'labor_charges', 'transport_charges', 'freight_charges', 'bank_charges', 'other_charges',
        'submitted_by_user_id', 'submitted_at', 'approved_by_user_id', 'approved_at',
    ];

    public function storeRequisition()
    {
        return $this->belongsTo(StoreRequisition::class, 'store_requisition_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function items()
    {
        return $this->hasMany(LocalPurchaseOrderItem::class, 'local_purchase_order_id');
    }

    public function statusHistory()
    {
        return $this->hasMany(LpoStatusHistory::class, 'local_purchase_order_id')->orderBy('changed_at');
    }

    public function logStatusChange(string $toStatus, int $byUserId, ?string $remark = null): void
    {
        $this->statusHistory()->create([
            'from_status' => $this->getOriginal('status'),
            'to_status' => $toStatus,
            'changed_by_user_id' => $byUserId,
            'remark' => $remark,
            'changed_at' => now(),
        ]);
    }

    public function grn()
    {
        return $this->hasOne(GrnPurchaseOrder::class, 'local_purchase_order_id');
    }
}