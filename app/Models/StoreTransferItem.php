<?php
// app/Models/StoreTransferItem.php
namespace App\Models;

use App\Models\StoreTransferBatchAllocation;
use Illuminate\Database\Eloquent\Model;

class StoreTransferItem extends Model
{
    protected $table = 'tbl_store_transfer_items';
    protected $fillable = ['transfer_id', 'item_id', 'quantity'];

    public function item() { return $this->belongsTo(Item::class); }
    public function allocations() { return $this->hasMany(StoreTransferBatchAllocation::class, 'transfer_item_id'); }
}