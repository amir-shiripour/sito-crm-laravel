<?php

namespace Modules\Market\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WarehouseTransfer extends Model
{
    use HasFactory;

    protected $table = 'market_warehouse_transfers';

    protected $fillable = [
        'source_warehouse_id',
        'destination_warehouse_id',
        'product_variant_id',
        'vendor_product_id',
        'quantity',
        'status', // pending, approved, rejected
        'rejection_reason',
        'user_id',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    public function sourceWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    public function destinationWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function vendorProduct()
    {
        return $this->belongsTo(VendorProduct::class, 'vendor_product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
