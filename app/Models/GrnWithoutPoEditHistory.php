<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrnWithoutPoEditHistory extends Model
{
    protected $table = 'tbl_grn_without_po_edit_history';

    protected $fillable = ['grn_id', 'edited_by_user_id', 'previous_header', 'previous_items', 'edited_at'];

    protected $casts = [
        'previous_header' => 'array',
        'previous_items' => 'array',
        'edited_at' => 'datetime',
    ];

    public function editedBy()
    {
        return $this->belongsTo(User::class, 'edited_by_user_id');
    }
}