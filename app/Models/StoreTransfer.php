<?php
// app/Models/StoreTransfer.php
namespace App\Models;

use App\Models\StoreTransferItem;
use Illuminate\Database\Eloquent\Model;

class StoreTransfer extends Model
{
    protected $table = 'tbl_store_transfers';

    protected $fillable = [
        'from_subdepartment_id', 'to_subdepartment_id', 'created_by_user_id', 'transfer_date', 'description', 'status',
        'submitted_by_user_id', 'submitted_at', 'approved_by_user_id', 'approved_at',
        'received_by_user_id', 'received_at', 'cancelled_by_user_id', 'cancelled_at', 'cancel_reason',
    ];

    public function fromSubdepartment() { return $this->belongsTo(Subdepartment::class, 'from_subdepartment_id', 'Subdepartment_ID'); }
    public function toSubdepartment() { return $this->belongsTo(Subdepartment::class, 'to_subdepartment_id', 'Subdepartment_ID'); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function approvedBy() { return $this->belongsTo(User::class, 'approved_by_user_id'); }
    public function receivedBy() { return $this->belongsTo(User::class, 'received_by_user_id'); }
    public function items() { return $this->hasMany(StoreTransferItem::class, 'transfer_id'); }
    public function cancelledBy() { return $this->belongsTo(User::class, 'cancelled_by_user_id'); }
}