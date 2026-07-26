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

    public function company()
    {
        return $this->belongsTo(Company::class, 'Company_ID', 'Company_ID');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_branches', 'branch_id', 'user_id', 'Branch_ID', 'id');
    }
}
