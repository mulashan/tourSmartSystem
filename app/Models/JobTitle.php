<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobTitle extends Model
{
    protected $table = 'job_titles';

    protected $fillable = [
        'title',
        'title_location',
    ];
}
