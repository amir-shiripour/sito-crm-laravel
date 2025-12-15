<?php

namespace Modules\Booking\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class BookingSlotHold extends Model
{
    protected $table = 'booking_slot_holds';

    public $timestamps = false;

    protected $fillable = [
        'service_id',
        'provider_user_id',
        'client_temp_key',
        'start_at_utc',
        'end_at_utc',
        'expires_at_utc',
        'created_at',
    ];

    protected $casts = [
        'start_at_utc' => 'datetime',
        'end_at_utc' => 'datetime',
        'expires_at_utc' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_user_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(BookingService::class, 'service_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at_utc && $this->expires_at_utc->lte(now());
    }
}
