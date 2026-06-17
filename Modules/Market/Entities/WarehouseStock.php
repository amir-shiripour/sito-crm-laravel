<?php

namespace Modules\Market\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WarehouseStock extends Model
{
    use HasFactory;

    protected $table = 'market_warehouse_stocks';

    protected $fillable = [
        'warehouse_id',
        'product_variant_id',
        'vendor_product_id',
        'physical_stock',
        'online_stock', // 💡 اضافه شد
        'reserved_stock',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function vendorProduct()
    {
        return $this->belongsTo(VendorProduct::class, 'vendor_product_id');
    }

    protected static function newFactory()
    {
        // return \Modules\Market\Database\factories\WarehouseStockFactory::new();
    }
}
