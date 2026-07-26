<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreRequisition extends Model
{
    protected $table = 'tbl_store_requisitions';

    protected $fillable = [
        'order_date', 'subdepartment_id', 'prepared_by_user_id',
        'priority_status', 'emergency_reason', 'order_description',
        'is_user_store_order', 'status', 'approved_by_user_id', 'approved_at',
        'procurement_status','rejection_reason',
    ];

    protected $casts = [
        'is_user_store_order' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function subdepartment()
    {
        return $this->belongsTo(Subdepartment::class, 'subdepartment_id', 'Subdepartment_ID');
    }

    public function preparedBy()
    {
        return $this->belongsTo(User::class, 'prepared_by_user_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function items()
    {
        return $this->hasMany(StoreRequisitionItem::class, 'store_requisition_id');
    }

    public function localPurchaseOrder()
    {
        return $this->hasOne(LocalPurchaseOrder::class, 'store_requisition_id');
    }

    public function getProcurementStatusLabelAttribute(): string
    {
        if ($this->procurement_status === 'rejected') {
            return 'Rejected by Procurement';
        }

        $lpo = $this->localPurchaseOrder;

        if (! $lpo) {
            return 'Pending Procurement';
        }

        return match ($lpo->status) {
            'draft' => 'LPO Created (Draft)',
            'pending_approval' => 'LPO Pending Approval',
            'approved' => 'LPO Approved',
            default => ucfirst($lpo->status),
        };
    }
}