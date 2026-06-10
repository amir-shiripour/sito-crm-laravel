<?php

namespace Modules\Market\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShippingSlotBooking extends Model
{
    use HasFactory;

    protected $table = 'market_shipping_slot_bookings';

    protected $fillable = [
        'shipping_slot_id',
        'booking_date',
        'orders_count',
    ];

    protected $casts = [
        'booking_date' => 'date',
    ];

    public function slot()
    {
        return $this->belongsTo(ShippingSlot::class, 'shipping_slot_id');
    }
}
