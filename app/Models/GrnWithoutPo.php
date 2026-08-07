<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrnWithoutPo extends Model
{
    protected $table = 'tbl_grn_without_po';

    protected $fillable = [
        'subdepartment_id', 'supplier_id', 'created_by_user_id',
        'purchase_description', 'delivery_note_number', 'delivery_note_attachment',
        'invoice_number', 'invoice_attachment', 'delivery_date', 'delivery_person',
        'status', 'approved_by_user_id', 'approved_at','vat_charges', 'transport_charges', 'labor_charges', 'bank_charges', 'freight_charges', 'other_charges',
    ];

    public function subdepartment()
    {
        return $this->belongsTo(Subdepartment::class, 'subdepartment_id', 'Subdepartment_ID');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function items()
    {
        return $this->hasMany(GrnWithoutPoItem::class, 'grn_id');
    }

    public function editHistory()
    {
        return $this->hasMany(GrnWithoutPoEditHistory::class, 'grn_id')->orderByDesc('edited_at');
    }
}