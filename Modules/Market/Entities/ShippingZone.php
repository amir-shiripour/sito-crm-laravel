<?php

namespace Modules\Market\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShippingZone extends Model
{
    use HasFactory;

    protected $table = 'market_shipping_zones';

    protected $fillable = [
        'name',
        'states',
        'cities',
        'is_active',
    ];

    protected $casts = [
        'states' => 'array',
        'cities' => 'array',
        'is_active' => 'boolean',
    ];

    public function rates()
    {
        return $this->hasMany(ShippingRate::class, 'shipping_zone_id');
    }
}
