<?php

namespace Modules\Market\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WarehouseTransaction extends Model
{
    use HasFactory;

    protected $table = 'market_warehouse_transactions';

    protected $fillable = [
        'warehouse_id',
        'product_variant_id',
        'vendor_product_id',
        'type',
        'quantity',
        'unit_price',
        'reference_type',
        'reference_id',
        'description',
        'user_id',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    protected static function newFactory()
    {
        // return \Modules\Market\Database\factories\WarehouseTransactionFactory::new();
    }
}
