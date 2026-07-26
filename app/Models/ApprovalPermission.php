<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalPermission extends Model
{
    protected $table = 'tbl_approval_permissions';

    protected $fillable = ['key', 'label', 'description'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_approval_permissions', 'approval_permission_id', 'user_id');
    }
}