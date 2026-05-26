<?php

namespace Modules\Market\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Clients\Entities\Client; // 💡 مدل Client اضافه شد

class Order extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'market_orders';

    protected $fillable = [
        'client_id', // 💡 جایگزین user_id شد
        'name',
        'mobile',
        'province_id',
        'city_id',
        'address',
        'payment_method',
        'total_amount',
        'status', // pending, processing, completed, canceled, failed
        'transaction_id',
        'payment_ref_id',
        'paid_at',
        // ستون‌های جدید از مایگریشن
        'total_items_price',
        'total_shipping_cost',
        'total_tax',
        'total_discount',
        'grand_total',
        'shipping_address_json',
        'shipping_method',
        'tracking_code',
        'payment_status',
        'delivery_status',
        'customer_notes',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'shipping_address_json' => 'array',
    ];

    /**
     * Get the client that owns the order.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function meta()
    {
        return $this->hasMany(OrderMeta::class, 'order_id');
    }

    public function syncLogs()
    {
        return $this->hasMany(OrderSyncLog::class, 'order_id');
    }

    public function checkoutForm()
    {
        return $this->belongsTo(CheckoutForm::class);
    }

    protected static function newFactory()
    {
        // return \Modules\Market\Database\factories\OrderFactory::new();
    }
}
