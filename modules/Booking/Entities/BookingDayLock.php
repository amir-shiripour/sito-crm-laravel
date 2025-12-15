<?php

namespace Modules\Booking\Entities;

use Illuminate\Database\Eloquent\Model;

class BookingDayLock extends Model
{
    protected $table = 'booking_day_locks';

    protected $fillable = [
        'service_id',
        'provider_user_id',
        'local_date',
    ];

    protected $casts = [
        'local_date' => 'date',
    ];
}
