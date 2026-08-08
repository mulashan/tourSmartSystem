<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceUse extends Model
{
    protected $table = 'tbl_service_use';

    protected $fillable = ['requisition_date', 'subdepartment_id', 'officer_user_id', 'reason'];

    public function subdepartment() { return $this->belongsTo(Subdepartment::class, 'subdepartment_id', 'Subdepartment_ID'); }
    public function officer() { return $this->belongsTo(User::class, 'officer_user_id'); }
    public function items() { return $this->hasMany(ServiceUseItem::class, 'service_use_id'); }
}