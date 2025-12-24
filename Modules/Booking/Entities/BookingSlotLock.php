<?php

namespace Modules\Booking\Entities;

use Illuminate\Database\Eloquent\Model;

class BookingSlotLock extends Model
{
    protected $table = 'booking_slot_locks';

    protected $fillable = [
        'service_id',
        'provider_user_id',
        'start_at_utc',
        'end_at_utc',
    ];

    protected $casts = [
        'start_at_utc' => 'datetime',
        'end_at_utc' => 'datetime',
    ];
}
