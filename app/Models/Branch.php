<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $table = 'tbl_branches';

    protected $primaryKey = 'Branch_ID';

    public $timestamps = false;

    protected $fillable = [
        'Branch_Name',
        'Location',
        'Manager',
        'status',
        'token',
        'token_date',
        'BannerLink',
        'Company_ID',
    ];

    protected $casts = [
        'token_date' => 'datetime',
    ];
}
