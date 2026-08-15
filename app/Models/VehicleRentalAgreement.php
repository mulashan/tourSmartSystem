<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleRentalAgreement extends Model
{
    protected $table = 'tbl_vehicle_rental_agreements';
    protected $fillable = ['vehicle_id', 'owner', 'start_date', 'end_date', 'agreement_document', 'contact_info', 'created_by_user_id'];

    public function createdBy() { return $this->belongsTo(User::class, 'created_by_user_id'); }
}