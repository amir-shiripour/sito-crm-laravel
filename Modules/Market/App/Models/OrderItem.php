<?php

namespace Modules\Market\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'market_order_items';

    protected $fillable = [
        'order_id',
        'vendor_product_id',
        'vendor_id',
        'product_title',
        'quantity',
        'unit_price',
        'unit_tax',
        'total_price',
        'vendor_commission_rate',
    ];

    public $timestamps = false;

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Note: We are linking to ProductVariant but naming it 'product' for simplicity.
     * You can rename this to 'variant' if you prefer more clarity.
     */
    public function product()
    {
        return $this->belongsTo(\Modules\Market\Entities\ProductVariant::class, 'product_id');
    }

    public function vendorProduct()
    {
        return $this->belongsTo(\Modules\Market\Entities\VendorProduct::class, 'vendor_product_id');
    }

    public function vendor()
    {
        return $this->belongsTo(\Modules\Market\Entities\Vendor::class, 'vendor_id');
    }

    protected static function newFactory()
    {
        // return \Modules\Market\Database\factories\OrderItemFactory::new();
    }
}
