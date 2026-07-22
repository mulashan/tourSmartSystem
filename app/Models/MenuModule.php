<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuModule extends Model
{
    protected $table = 'tbl_menus';

    protected $primaryKey = 'module_id';

    public $timestamps = false;

    protected $fillable = [
        'module_id',
        'name',
        'label',
        'menu_icon',
        'route_path',
        'parent_id',
        'parent_id2',
        'is_menu',
        'description',
        'is_dashboard',
        'collapse',
        'new_message',
    ];

    protected $casts = [
        'is_menu' => 'boolean',
        'is_dashboard' => 'integer',
        'collapse' => 'integer',
        'new_message' => 'integer',
    ];
}
