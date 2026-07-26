<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMenuPermission extends Model
{
    protected $table = 'user_menu_permissions';

    protected $fillable = ['user_id', 'menu_key', 'can_access'];

    protected $casts = ['can_access' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}