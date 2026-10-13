<?php

namespace Modules\Properties\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Clients\Entities\Client;
use Carbon\Carbon;
use Morilog\Jalali\Jalalian;

class PropertyRentalBooking extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'property_rental_bookings';

    // وضعیت‌های چرخه حیات رزرو
    public const STATUS_PENDING_HOST         = 'pending_host';
    public const STATUS_AWAITING_PAYMENT     = 'awaiting_payment';
    public const STATUS_CONFIRMED            = 'confirmed';
    public const STATUS_CHECKED_IN           = 'checked_in';
    public const STATUS_COMPLETED            = 'completed';
    public const STATUS_REJECTED             = 'rejected';
    public const STATUS_CANCELLED_BY_GUEST   = 'cancelled_by_guest';
    public const STATUS_CANCELLED_BY_HOST    = 'cancelled_by_host';
    public const STATUS_CANCELLED_BY_ADMIN   = 'cancelled_by_admin';
    public const STATUS_EXPIRED              = 'expired';

    // وضعیت‌های پرداخت
    public const PAYMENT_UNPAID          = 'unpaid';
    public const PAYMENT_PARTIALLY_PAID  = 'partially_paid';
    public const PAYMENT_PAID            = 'paid';
    public const PAYMENT_REFUNDED        = 'refunded';

    protected $fillable = [
        'booking_code',
        'property_id',
        'host_id',
        'client_id',
        'check_in_date',
        'check_out_date',
        'check_in_jalali',
        'check_out_jalali',
        'nights_count',
        'guests_count',
        'extra_guests_count',
        'nights_total',
        'extra_guests_fee',
        'cleaning_fee',
        'discount_amount',
        'grand_total',
        'prepayment_amount',
        'paid_amount',
        'commission_percent',
        'commission_amount',
        'host_payout_amount',
        'status',
        'payment_status',
        'payment_method',
        'payment_deadline_at',
        'host_action_at',
        'confirmed_at',
        'cancelled_at',
        'cancellation_reason',
        'notes',
        'guest_full_name',
        'guest_phone',
        'guest_national_code',
        'meta',
    ];

    protected $casts = [
        'check_in_date'        => 'date',
        'check_out_date'       => 'date',
        'nights_count'         => 'integer',
        'guests_count'         => 'integer',
        'extra_guests_count'   => 'integer',
        'nights_total'         => 'decimal:0',
        'extra_guests_fee'     => 'decimal:0',
        'cleaning_fee'         => 'decimal:0',
        'discount_amount'      => 'decimal:0',
        'grand_total'          => 'decimal:0',
        'prepayment_amount'    => 'decimal:0',
        'paid_amount'          => 'decimal:0',
        'commission_percent'   => 'decimal:2',
        'commission_amount'    => 'decimal:0',
        'host_payout_amount'   => 'decimal:0',
        'payment_deadline_at'  => 'datetime',
        'host_action_at'       => 'datetime',
        'confirmed_at'         => 'datetime',
        'cancelled_at'         => 'datetime',
        'meta'                 => 'array',
    ];

    // روابط (Relationships)
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(PropertyHost::class, 'host_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(PropertyRentalBlock::class, 'booking_id');
    }

    public function rentalBlock()
    {
        return $this->hasOne(PropertyRentalBlock::class, 'booking_id');
    }

    // عناوین و برچسب‌های فارسی وضعیت‌ها
    public static function getStatusesList(): array
    {
        return [
            self::STATUS_PENDING_HOST       => 'در انتظار تأیید میزبان',
            self::STATUS_AWAITING_PAYMENT   => 'در انتظار پرداخت',
            self::STATUS_CONFIRMED          => 'تأیید شده و قطعی',
            self::STATUS_CHECKED_IN         => 'پذیرش شده (در حال اقامت)',
            self::STATUS_COMPLETED          => 'پایان اقامت (تخلیه شده)',
            self::STATUS_REJECTED           => 'رد شده توسط میزبان',
            self::STATUS_CANCELLED_BY_GUEST => 'لغو توسط مهمان',
            self::STATUS_CANCELLED_BY_HOST  => 'لغو توسط میزبان',
            self::STATUS_CANCELLED_BY_ADMIN => 'لغو اداری توسط مدیریت',
            self::STATUS_EXPIRED            => 'منقضی شده (عدم پرداخت)',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatusesList()[$this->status] ?? $this->status;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING_HOST       => 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400 border border-amber-200/80 dark:border-amber-800/40',
            self::STATUS_AWAITING_PAYMENT   => 'bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-400 border border-blue-200/80 dark:border-blue-800/40',
            self::STATUS_CONFIRMED          => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/40',
            self::STATUS_CHECKED_IN         => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-800/40',
            self::STATUS_COMPLETED          => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700',
            self::STATUS_REJECTED,
            self::STATUS_CANCELLED_BY_GUEST,
            self::STATUS_CANCELLED_BY_HOST,
            self::STATUS_CANCELLED_BY_ADMIN,
            self::STATUS_EXPIRED            => 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/40',
            default                         => 'bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-400 border border-gray-200 dark:border-gray-700',
        };
    }

    public static function getPaymentStatusesList(): array
    {
        return [
            self::PAYMENT_UNPAID          => 'پرداخت نشده',
            self::PAYMENT_PARTIALLY_PAID  => 'پرداخت پیش‌پرداخت',
            self::PAYMENT_PAID            => 'تسویه کامل',
            self::PAYMENT_REFUNDED        => 'مسترد شده',
        ];
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return self::getPaymentStatusesList()[$this->payment_status] ?? $this->payment_status;
    }

    public function getPaymentStatusBadgeClassAttribute(): string
    {
        return match($this->payment_status) {
            self::PAYMENT_UNPAID         => 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/40',
            self::PAYMENT_PARTIALLY_PAID => 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400 border border-amber-200/80 dark:border-amber-800/40',
            self::PAYMENT_PAID           => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/40',
            self::PAYMENT_REFUNDED       => 'bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-400 border border-purple-200/80 dark:border-purple-800/40',
            default                      => 'bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-400 border border-gray-200 dark:border-gray-700',
        };
    }

    public function getStatusBadgeAttribute(): array
    {
        return [
            'label' => $this->status_label,
            'class' => $this->status_badge_class,
        ];
    }

    public function getPaymentStatusBadgeAttribute(): array
    {
        return [
            'label' => $this->payment_status_label,
            'class' => $this->payment_status_badge_class,
        ];
    }

    public function getTotalPriceAttribute(): float
    {
        return (float) ($this->grand_total ?? 0);
    }

    public function getNightsPriceAttribute(): float
    {
        return (float) ($this->nights_total ?? 0);
    }

    public function getCheckInAttribute(): ?string
    {
        return $this->check_in_jalali ?: ($this->check_in_date ? \Morilog\Jalali\Jalalian::fromCarbon($this->check_in_date)->format('Y/m/d') : null);
    }

    public function getCheckOutAttribute(): ?string
    {
        return $this->check_out_jalali ?: ($this->check_out_date ? \Morilog\Jalali\Jalalian::fromCarbon($this->check_out_date)->format('Y/m/d') : null);
    }

    public function getCurrencyAttribute(): string
    {
        return PropertySetting::get('rental_currency', 'IRT');
    }

    // متدهای کمکی منطق کسب‌وکار
    public function isConfirmed(): bool
    {
        return in_array($this->status, [self::STATUS_CONFIRMED, self::STATUS_CHECKED_IN]);
    }

    public function canBeApprovedByHost(): bool
    {
        return $this->status === self::STATUS_PENDING_HOST;
    }

    public function canBeRejectedByHost(): bool
    {
        return $this->status === self::STATUS_PENDING_HOST;
    }

    public function canBePaid(): bool
    {
        if ($this->status !== self::STATUS_AWAITING_PAYMENT) {
            return false;
        }

        if ($this->payment_deadline_at && $this->payment_deadline_at->isPast()) {
            return false;
        }

        return true;
    }

    public function canBeCancelledByGuest(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING_HOST,
            self::STATUS_AWAITING_PAYMENT,
            self::STATUS_CONFIRMED,
        ]);
    }

    // تولید کد یکتای رزرو
    public static function generateBookingCode(?int $propertyId = null): string
    {
        $prefix = 'RB';
        $prop = $propertyId ? str_pad((string)$propertyId, 4, '0', STR_PAD_LEFT) : '0000';
        $rand = strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        $date = date('ymd');

        return "{$prefix}-{$date}-{$prop}-{$rand}";
    }
}
