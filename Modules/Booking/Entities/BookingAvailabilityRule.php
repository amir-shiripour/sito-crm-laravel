<?php

namespace Modules\Booking\Entities;

use Illuminate\Database\Eloquent\Model;

class BookingAvailabilityRule extends Model
{
    protected $table = 'booking_availability_rules';

    public const SCOPE_GLOBAL = 'GLOBAL';
    public const SCOPE_SERVICE = 'SERVICE';
    public const SCOPE_SERVICE_PROVIDER = 'SERVICE_PROVIDER';

    protected $fillable = [
        'scope_type',
        'scope_id',
        'weekday',
        'is_closed',
        'work_start_local',
        'work_end_local',
        'breaks_json',
        'slot_duration_minutes',
        'capacity_per_slot',
        'capacity_per_day',
        'buffer_before_minutes',
        'buffer_after_minutes',
    ];

    protected $casts = [
        'breaks_json' => 'array',
        'is_closed' => 'bool',
        'weekday' => 'int',
        'slot_duration_minutes' => 'int',
        'capacity_per_slot' => 'int',
        'capacity_per_day' => 'int',
        'buffer_before_minutes' => 'int',
        'buffer_after_minutes' => 'int',
    ];
}
