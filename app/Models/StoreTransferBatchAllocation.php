<?php
// app/Models/StoreTransferBatchAllocation.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreTransferBatchAllocation extends Model
{
    protected $table = 'tbl_store_transfer_batch_allocations';
    protected $fillable = ['transfer_item_id', 'stock_batch_id', 'quantity_allocated'];

    public function stockBatch() { return $this->belongsTo(StockBatch::class); }
}