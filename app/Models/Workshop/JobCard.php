<?php

namespace App\Models\Workshop;

use App\Models\User;
use App\Models\Workshop\Concerns\TracksWorkshopUsers;
use Illuminate\Database\Eloquent\Model;

class JobCard extends Model
{
    use TracksWorkshopUsers;

    public const STATUSES = ['new', 'assigned', 'in_progress', 'waiting_parts', 'completed', 'invoiced', 'closed', 'cancelled'];

    protected $fillable = [
        'job_no',
        'customer_id',
        'vehicle_id',
        'opened_by',
        'opened_date',
        'odometer_reading',
        'fuel_level',
        'reported_problems',
        'priority',
        'status',
        'remarks',
        'expected_completion',
        'completed_date',
    ];

    protected $casts = [
        'opened_date' => 'date',
        'expected_completion' => 'date',
        'completed_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function repairOrders()
    {
        return $this->hasMany(RepairOrder::class);
    }

    public function diagnosis()
    {
        return $this->hasOne(Diagnosis::class);
    }

    public function vehicleInspections()
    {
        return $this->hasMany(VehicleInspection::class);
    }

    public function mechanicAssignments()
    {
        return $this->hasMany(JobMechanic::class);
    }

    public function labourEntries()
    {
        return $this->hasMany(LabourEntry::class);
    }

    public function partsUsed()
    {
        return $this->hasMany(PartUsed::class);
    }

    public function completion()
    {
        return $this->hasOne(JobCompletion::class);
    }

    public function qualityCheck()
    {
        return $this->hasOne(QualityCheck::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
}
