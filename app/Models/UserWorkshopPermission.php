<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWorkshopPermission extends Model
{
    protected $fillable = ['user_id', 'permission_key'];
}
