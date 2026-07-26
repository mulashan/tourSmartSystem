<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LpoStatusHistory extends Model
{
    protected $table = 'tbl_lpo_status_history';

    protected $fillable = ['local_purchase_order_id', 'from_status', 'to_status', 'changed_by_user_id', 'remark', 'changed_at'];

    public $timestamps = false;

    protected $casts = ['changed_at' => 'datetime'];

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}