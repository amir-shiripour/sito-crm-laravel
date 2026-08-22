<?php

namespace Modules\Booking\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Modules\Clients\Entities\Client;
use App\Models\User;

class BookingWaitlist extends Model
{
    use SoftDeletes;

    protected $table = 'booking_waitlists';

    public const STATUS_WAITING     = 'waiting';
    public const STATUS_NOTIFIED    = 'notified';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_CONVERTED   = 'converted';
    public const STATUS_CANCELED    = 'canceled';

    protected $fillable = [
        'client_id',
        'service_id',
        'provider_user_id',
        'preferred_date',
        'notes',
        'position',
        'status',
        'appointment_id',
        'created_by_user_id',
        'notified_at',
        'converted_at',
    ];

    protected $casts = [
        'preferred_date' => 'date',
        'notified_at'    => 'datetime',
        'converted_at'   => 'datetime',
        'position'       => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (BookingWaitlist $model) {
            if (!$model->position || $model->position <= 1) {
                $maxPos = static::query()
                    ->where('status', self::STATUS_WAITING)
                    ->when($model->service_id, fn($q) => $q->where('service_id', $model->service_id), fn($q) => $q->whereNull('service_id'))
                    ->max('position');

                $model->position = ($maxPos ? (int)$maxPos : 0) + 1;
            }
        });
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_WAITING     => 'در انتظار',
            self::STATUS_NOTIFIED    => 'اطلاع‌رسانی شده',
            self::STATUS_IN_PROGRESS => 'در حال بررسی',
            self::STATUS_CONVERTED   => 'تبدیل به نوبت',
            self::STATUS_CANCELED    => 'لغو شده',
        ];
    }

    public static function statusColors(): array
    {
        return [
            self::STATUS_WAITING     => 'amber',
            self::STATUS_NOTIFIED    => 'indigo',
            self::STATUS_IN_PROGRESS => 'blue',
            self::STATUS_CONVERTED   => 'emerald',
            self::STATUS_CANCELED    => 'rose',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return static::statusLabels()[$this->status] ?? $this->status;
    }

    /**
     * رتبه و موقعیت واقعی و لحظه‌ای مراجع در این صف
     */
    public function getQueueRankAttribute(): int
    {
        if (!in_array($this->status, [self::STATUS_WAITING, self::STATUS_NOTIFIED, self::STATUS_IN_PROGRESS])) {
            return (int)$this->position;
        }

        return static::query()
            ->whereIn('status', [self::STATUS_WAITING, self::STATUS_NOTIFIED, self::STATUS_IN_PROGRESS])
            ->when($this->service_id, fn($q) => $q->where('service_id', $this->service_id), fn($q) => $q->whereNull('service_id'))
            ->when($this->provider_user_id, fn($q) => $q->where('provider_user_id', $this->provider_user_id))
            ->where('id', '<', $this->id)
            ->count() + 1;
    }

    /**
     * تعداد کل افراد حاضر در این صف
     */
    public function getQueueTotalAttribute(): int
    {
        return static::query()
            ->whereIn('status', [self::STATUS_WAITING, self::STATUS_NOTIFIED, self::STATUS_IN_PROGRESS])
            ->when($this->service_id, fn($q) => $q->where('service_id', $this->service_id), fn($q) => $q->whereNull('service_id'))
            ->when($this->provider_user_id, fn($q) => $q->where('provider_user_id', $this->provider_user_id))
            ->count();
    }

    /**
     * تعداد افراد جلوتر در صف
     */
    public function getQueueAheadCountAttribute(): int
    {
        return max(0, $this->queue_rank - 1);
    }

    /**
     * محاسبه رتبه برای ورودی جدید قبل از ثبت
     */
    public static function getNextPositionFor(?int $serviceId = null, ?int $providerId = null): int
    {
        return static::query()
            ->whereIn('status', [self::STATUS_WAITING, self::STATUS_NOTIFIED, self::STATUS_IN_PROGRESS])
            ->when($serviceId, fn($q) => $q->where('service_id', $serviceId), fn($q) => $q->whereNull('service_id'))
            ->when($providerId, fn($q) => $q->where('provider_user_id', $providerId))
            ->count() + 1;
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(BookingService::class, 'service_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_user_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function scopeWaiting(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_WAITING);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_WAITING, self::STATUS_NOTIFIED, self::STATUS_IN_PROGRESS]);
    }

    public function scopeForService(Builder $query, ?int $serviceId): Builder
    {
        if ($serviceId) {
            return $query->where('service_id', $serviceId);
        }
        return $query->whereNull('service_id');
    }

    public function scopeGeneral(Builder $query): Builder
    {
        return $query->whereNull('service_id');
    }

    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function convertToAppointment(int $appointmentId): bool
    {
        $this->status = self::STATUS_CONVERTED;
        $this->appointment_id = $appointmentId;
        $this->converted_at = now();
        $saved = $this->save();

        if ($saved) {
            event('booking.waitlist.converted', [$this]);
        }

        return $saved;
    }
}
