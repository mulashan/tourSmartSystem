<?php

namespace App\Models;

use App\Models\GrnAgainstIssueNote;
use App\Models\IssueNoteItem;
use Illuminate\Database\Eloquent\Model;

class IssueNote extends Model
{
    protected $table = 'tbl_issue_notes';

    protected $fillable = ['requisition_id', 'officer_user_id', 'issue_date', 'status', 'approved_by_user_id', 'approved_at'];

    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
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
        return $this->hasMany(IssueNoteItem::class, 'issue_note_id');
    }
    public function grnAgainstIssueNote()
    {
        return $this->hasOne(GrnAgainstIssueNote::class, 'issue_note_id');
    }
}