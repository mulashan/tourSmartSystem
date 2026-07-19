<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTypeMenuPermission extends Model
{
    protected $fillable = [
        'privilege_id',
        'menu_key',
        'can_access',
    ];

    protected $casts = [
        'can_access' => 'boolean',
    ];
}
