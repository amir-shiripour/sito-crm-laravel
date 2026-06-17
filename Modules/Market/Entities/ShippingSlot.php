<?php

namespace Modules\Market\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShippingSlot extends Model
{
    use HasFactory;

    protected $table = 'market_shipping_slots';

    protected $fillable = [
        'shipping_method_id',
        'days',
        'states',
        'cities',
        'start_time',
        'end_time',
        'capacity',
    ];

    protected $casts = [
        'days' => 'array',
        'states' => 'array',
        'cities' => 'array',
    ];

    public function method()
    {
        return $this->belongsTo(ShippingMethod::class, 'shipping_method_id');
    }

    public function bookings()
    {
        return $this->hasMany(ShippingSlotBooking::class, 'shipping_slot_id');
    }
}
