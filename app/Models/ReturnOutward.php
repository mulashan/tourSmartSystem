<?php

namespace App\Models;

use App\Models\ReturnOutwardItem;
use Illuminate\Database\Eloquent\Model;

class ReturnOutward extends Model
{
    protected $table = 'tbl_return_outward';

    protected $fillable = [
        'transaction_date', 'subdepartment_id', 'supplier_id', 'posted_by_user_id', 'status',
        'submitted_by_user_id', 'submitted_at', 'approved_by_user_id', 'approved_at','description',
    ];

    public function subdepartment() { return $this->belongsTo(Subdepartment::class, 'subdepartment_id', 'Subdepartment_ID'); }
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function postedBy() { return $this->belongsTo(User::class, 'posted_by_user_id'); }
    public function approvedBy() { return $this->belongsTo(User::class, 'approved_by_user_id'); }
    public function items() { return $this->hasMany(ReturnOutwardItem::class, 'return_id'); }
}