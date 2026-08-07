<?php

namespace App\Models;

use App\Models\StockAdjustmentItem;
use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    protected $table = 'tbl_stock_adjustments';

    protected $fillable = [
        'adjustment_date', 'subdepartment_id', 'officer_user_id', 'description', 'reason', 'status',
        'submitted_by_user_id', 'submitted_at', 'approved_by_user_id', 'approved_at',
    ];

    public function subdepartment() { return $this->belongsTo(Subdepartment::class, 'subdepartment_id', 'Subdepartment_ID'); }
    public function officer() { return $this->belongsTo(User::class, 'officer_user_id'); }
    public function approvedBy() { return $this->belongsTo(User::class, 'approved_by_user_id'); }
    public function items() { return $this->hasMany(StockAdjustmentItem::class, 'adjustment_id'); }
}