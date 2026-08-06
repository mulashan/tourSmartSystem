<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrnOpenBalance extends Model
{
    protected $table = 'tbl_grn_open_balance';

    protected $fillable = [
        'subdepartment_id', 'created_by_user_id', 'creation_date', 'description',
        'status', 'submitted_by_user_id', 'submitted_at', 'approved_by_user_id', 'approved_at',
    ];

    public function subdepartment()
    {
        return $this->belongsTo(Subdepartment::class, 'subdepartment_id', 'Subdepartment_ID');
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
        return $this->hasMany(GrnOpenBalanceItem::class, 'grn_id');
    }
}