<?php

namespace Modules\Market\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShippingMethod extends Model
{
    use HasFactory;

    protected $table = 'market_shipping_methods';

    protected $fillable = [
        'name',
        'code',
        'driver',
        'settings',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function rates()
    {
        return $this->hasMany(ShippingRate::class, 'shipping_method_id');
    }

    public function slots()
    {
        return $this->hasMany(ShippingSlot::class, 'shipping_method_id');
    }
}
