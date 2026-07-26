<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'tbl_company';

    protected $primaryKey = 'Company_ID';

    public $timestamps = false;

    protected $fillable = ['Company_Name', 'Company_Logo', 'status'];
}