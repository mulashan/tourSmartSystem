<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleInsurance extends Model
{
    protected $table = 'tbl_vehicle_insurance';
    protected $fillable = [
        'vehicle_id', 'insurance_company', 'policy_number', 'insurance_type_id',
        'start_date', 'expire_date', 'premium', 'contact', 'certificate_document', 'status', 'created_by_user_id',
    ];

    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function insuranceType() { return $this->belongsTo(Lookup::class, 'insurance_type_id'); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function coverages() { return $this->belongsToMany(Lookup::class, 'tbl_vehicle_insurance_coverages', 'insurance_id', 'coverage_id'); }
}