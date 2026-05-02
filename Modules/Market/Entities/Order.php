<?php

namespace Modules\Market\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Clients\Entities\Client; // اتصال به ماژول مشتریان

class Order extends Model
{
    use SoftDeletes;

    protected $table = 'market_orders';

    protected $fillable = [
        'client_id', // 💡 تغییر کلیدی
        'total_items_price', 'total_shipping_cost', 'total_tax',
        'total_discount', 'grand_total', 'shipping_address_json',
        'shipping_method', 'tracking_code', 'payment_method',
        'payment_status', 'delivery_status', 'customer_notes'
    ];

    protected $casts = [
        'shipping_address_json' => 'array',
    ];

    // خریداری که این سفارش را ثبت کرده (از جدول clients)
    public function customer()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
}
