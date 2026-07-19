<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPrivilege extends Model
{
    protected $table = 'tbl_users_privileges';

    protected $fillable = [
        'privilege_name',
        'privilege_desc',
        'access_level_id',
        'priv_status',
    ];

    protected $casts = [
        'priv_status' => 'boolean',
    ];
}
