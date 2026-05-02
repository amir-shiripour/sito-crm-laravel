<?php

namespace Modules\Market\Entities;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'market_order_items';

    protected $fillable = [
        'order_id',
        'vendor_product_id', // 💡 تغییر در اینجا
        'vendor_id',
        'product_title',
        'quantity',
        'unit_price',
        'unit_tax',
        'total_price',
        'vendor_commission_rate'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    // 💡 رابطه با محصول فروشنده
    public function vendorProduct()
    {
        return $this->belongsTo(VendorProduct::class, 'vendor_product_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }
}
