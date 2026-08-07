<?php

namespace App\Models;

use App\Models\GrnBatchAllocation;
use Illuminate\Database\Eloquent\Model;

class GrnAgainstIssueNoteItem extends Model
{
    protected $table = 'tbl_grn_against_issue_note_items';

    protected $fillable = ['grn_id', 'item_id', 'quantity'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function allocations()
    {
        return $this->hasMany(GrnBatchAllocation::class, 'grn_item_id');
    }
}