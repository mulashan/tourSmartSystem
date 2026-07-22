<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    protected $table = 'tbl_designation';

    protected $primaryKey = 'designation_ID';

    public $timestamps = false;

    protected $fillable = [
        'designation',
        'session_time_limit',
    ];
}
