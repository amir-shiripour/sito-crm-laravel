<?php

namespace Modules\Booking\Entities;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Clients\Entities\Client;

class Appointment extends Model
{
    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_PENDING = 'PENDING';
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
        'entry_at_utc',
        'exit_at_utc',
        'created_by_type',
        'created_by_user_id',
        'notes',
        'appointment_form_response_json',
        'rescheduled_from_appointment_id',
        'cancel_reason',
    ];

    protected $casts = [
        'appointment_form_response_json' => 'array',
    ];

    protected function startAtUtc(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->asUtcCarbon($value),
            set: fn($value) => $this->asUtcCarbon($value),
        );
    }

    protected function endAtUtc(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->asUtcCarbon($value),
            set: fn($value) => $this->asUtcCarbon($value),
        );
    }

    protected function entryAtUtc(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->asUtcCarbon($value),
            set: fn($value) => $this->asUtcCarbon($value),
        );
    }

    protected function exitAtUtc(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->asUtcCarbon($value),
            set: fn($value) => $this->asUtcCarbon($value),
        );
    }

    protected function asUtcCarbon(mixed $value): ?Carbon
    {
        if ($value === null) {
            return null;
        }

        $dt = $value instanceof Carbon
            ? $value
            : Carbon::parse($value, 'UTC');

        return $dt->copy()->setTimezone('UTC');
    }

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
        return $this->hasMany(BookingPayment::class, 'appointment_id');
    }

    public static function statusesList(): array
    {
        return [
            self::STATUS_DRAFT => ['label' => 'پیش‌نویس', 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'],
            self::STATUS_PENDING => ['label' => 'در انتظار تایید', 'class' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-200'],
            self::STATUS_PENDING_PAYMENT => ['label' => 'در انتظار پرداخت', 'class' => 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-200'],
            self::STATUS_CONFIRMED => ['label' => 'تایید شده', 'class' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200'],
            self::STATUS_CANCELED_BY_ADMIN => ['label' => 'لغو شده (ادمین)', 'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-200'],
            self::STATUS_CANCELED_BY_CLIENT => ['label' => 'لغو شده (مشتری)', 'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-200'],
            self::STATUS_NO_SHOW => ['label' => 'عدم حضور', 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'],
            self::STATUS_DONE => ['label' => 'انجام شده', 'class' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-200'],
            self::STATUS_RESCHEDULED => ['label' => 'جابجا شده', 'class' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-200'],
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        $normalized = strtoupper((string) $this->status);
        $list = self::statusesList();
        return $list[$normalized]['label'] ?? match($normalized) {
            'DRAFT' => 'پیش‌نویس',
            'PENDING' => 'در انتظار تایید',
            'PENDING_PAYMENT' => 'در انتظار پرداخت',
            'CONFIRMED' => 'تایید شده',
            'CANCELED_BY_ADMIN', 'CANCELLED_BY_ADMIN' => 'لغو شده (ادمین)',
            'CANCELED_BY_CLIENT', 'CANCELLED_BY_CLIENT' => 'لغو شده (مشتری)',
            'CANCELED', 'CANCELLED' => 'لغو شده',
            'NO_SHOW' => 'عدم حضور',
            'DONE', 'COMPLETED' => 'انجام شده',
            'RESCHEDULED' => 'جابجا شده',
            default => $this->status ?: 'نامشخص'
        };
    }
}
