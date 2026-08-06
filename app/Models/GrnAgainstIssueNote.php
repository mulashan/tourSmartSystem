<?php

namespace App\Models;

use App\Models\GrnAgainstIssueNoteItem;
use Illuminate\Database\Eloquent\Model;

class GrnAgainstIssueNote extends Model
{
    protected $table = 'tbl_grn_against_issue_note';

    protected $fillable = ['issue_note_id', 'created_by_user_id', 'receipt_date', 'status', 'approved_by_user_id', 'approved_at'];

    public function issueNote()
    {
        return $this->belongsTo(IssueNote::class);
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
        return $this->hasMany(GrnAgainstIssueNoteItem::class, 'grn_id');
    }
}