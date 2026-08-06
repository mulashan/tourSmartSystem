<?php

namespace App\Models;

use App\Models\IssueNote;
use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{
    protected $table = 'tbl_requisitions';

    protected $fillable = [
        'requisition_date', 'requesting_subdepartment_id', 'issuing_subdepartment_id', 'officer_user_id',
        'description', 'status', 'submitted_by_user_id', 'submitted_at', 'approved_by_user_id', 'approved_at',
    ];

    public function requestingSubdepartment()
    {
        return $this->belongsTo(Subdepartment::class, 'requesting_subdepartment_id', 'Subdepartment_ID');
    }

    public function issuingSubdepartment()
    {
        return $this->belongsTo(Subdepartment::class, 'issuing_subdepartment_id', 'Subdepartment_ID');
    }

    public function officer()
    {
        return $this->belongsTo(User::class, 'officer_user_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function items()
    {
        return $this->hasMany(RequisitionItem::class, 'requisition_id');
    }

    public function issueNote()
    {
        return $this->hasOne(IssueNote::class, 'requisition_id');
    }
}