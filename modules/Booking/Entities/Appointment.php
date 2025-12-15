<?php

namespace Modules\Booking\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Clients\Entities\Client;

class Appointment extends Model
{
    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_PENDING_PAYMENT = 'PENDING_PAYMENT';
    public const STATUS_CONFIRMED = 'CONFIRMED';
    public const STATUS_CANCELED_BY_CLIENT = 'CANCELED_BY_CLIENT';
    public const STATUS_CANCELED_BY_ADMIN = 'CANCELED_BY_ADMIN';
    public const STATUS_NO_SHOW = 'NO_SHOW';
    public const STATUS_DONE = 'DONE';
    public const STATUS_RESCHEDULED = 'RESCHEDULED';

    public const CREATED_BY_OPERATOR = 'OPERATOR';
    public const CREATED_BY_CLIENT_ONLINE = 'CLIENT_ONLINE';
    public const CREATED_BY_ADMIN = 'ADMIN';

    protected $table = 'appointments';

    protected $fillable = [
        'service_id',
        'provider_user_id',
        'client_id',
        'status',
        'start_at_utc',
        'end_at_utc',
        'created_by_type',
        'created_by_user_id',
        'notes',
        'appointment_form_response_json',
        'rescheduled_from_appointment_id',
        'cancel_reason',
    ];

    protected $casts = [
        'start_at_utc' => 'datetime',
        'end_at_utc' => 'datetime',
        'appointment_form_response_json' => 'array',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(BookingService::class, 'service_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_user_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'appointment_id');
    }
}
