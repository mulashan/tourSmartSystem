<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'tbl_suppliers';

    protected $fillable = [
        'supplier_name',
        'supplier_address',
        'postal_address',
        'contact_person_name',
        'contact_person_mobile',
        'contact_person_email',
        'telephone',
        'fax',
        'physical_address',
    ];
}