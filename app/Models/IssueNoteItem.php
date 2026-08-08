<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IssueNoteItem extends Model
{
    protected $table = 'tbl_issue_note_items';

    protected $fillable = ['issue_note_id', 'requisition_item_id', 'item_id', 'quantity_requested', 'quantity_issued'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function issueNote()
    {
        return $this->belongsTo(IssueNote::class);
    }
}