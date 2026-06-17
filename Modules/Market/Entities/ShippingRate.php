<?php

namespace Modules\Market\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShippingRate extends Model
{
    use HasFactory;

    protected $table = 'market_shipping_rates';

    protected $fillable = [
        'shipping_method_id',
        'shipping_zone_id',
        'min_weight',
        'max_weight',
        'min_order_price',
        'cost',
        'per_kg_cost',
    ];

    public function method()
    {
        return $this->belongsTo(ShippingMethod::class, 'shipping_method_id');
    }

    public function zone()
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }
}
