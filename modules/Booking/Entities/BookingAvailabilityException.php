<?php

namespace Modules\Booking\Entities;

use Illuminate\Database\Eloquent\Model;

class BookingAvailabilityException extends Model
{
    protected $table = 'booking_availability_exceptions';

    public const SCOPE_GLOBAL = 'GLOBAL';
    public const SCOPE_SERVICE = 'SERVICE';
    public const SCOPE_SERVICE_PROVIDER = 'SERVICE_PROVIDER';

    protected $fillable = [
        'scope_type',
        'scope_id',
        'local_date',
        'is_closed',
        'override_work_windows_json',
        'override_breaks_json',
        'override_capacity_per_slot',
        'override_capacity_per_day',
    ];

    protected $casts = [
        'local_date' => 'date',
        'is_closed' => 'bool',
        'override_work_windows_json' => 'array',
        'override_breaks_json' => 'array',
        'override_capacity_per_slot' => 'int',
        'override_capacity_per_day' => 'int',
    ];
}
