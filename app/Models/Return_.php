<?php

namespace App\Models;

use App\Models\ReturnItem;
use Illuminate\Database\Eloquent\Model;

class Return_ extends Model
{
    protected $table = 'tbl_returns';

    protected $fillable = [
        'return_date', 'from_subdepartment_id', 'to_subdepartment_id', 'posted_by_user_id', 'description', 'status',
        'submitted_by_user_id', 'submitted_at', 'approved_by_user_id', 'approved_at', 'received_by_user_id', 'received_at',
    ];

    public function fromSubdepartment()
    {
        return $this->belongsTo(Subdepartment::class, 'from_subdepartment_id', 'Subdepartment_ID');
    }

    public function toSubdepartment()
    {
        return $this->belongsTo(Subdepartment::class, 'to_subdepartment_id', 'Subdepartment_ID');
    }

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by_user_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    public function items()
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }
}