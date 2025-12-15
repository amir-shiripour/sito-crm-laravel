<?php

namespace Modules\Booking\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingServiceProvider extends Model
{
    public const OVERRIDE_MODE_INHERIT = 'INHERIT';
    public const OVERRIDE_MODE_OVERRIDE = 'OVERRIDE';

    protected $table = 'booking_service_providers';

    protected $fillable = [
        'service_id',
        'provider_user_id',
        'is_active',
        'customization_enabled',

        'override_price_mode',
        'override_base_price',
        'override_discount_price',
        'override_discount_from',
        'override_discount_to',

        'override_online_booking_mode',

        'override_status_mode',
        'override_status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'customization_enabled' => 'boolean',
        'override_discount_from' => 'datetime',
        'override_discount_to' => 'datetime',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(BookingService::class, 'service_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_user_id');
    }

    public function effectiveStatus(): string
    {
        $service = $this->service;

        if ($this->override_status_mode === self::OVERRIDE_MODE_OVERRIDE && $this->override_status) {
            return $this->override_status;
        }

        return $service?->status ?? BookingService::STATUS_INACTIVE;
    }

    public function effectiveOnlineBookingMode(): string
    {
        if ($this->override_online_booking_mode) {
            return $this->override_online_booking_mode;
        }

        return $this->service?->online_booking_mode ?? BookingService::ONLINE_MODE_INHERIT;
    }

    public function effectivePrice(): float
    {
        $service = $this->service;

        $now = now('UTC');

        $base = (float) ($service?->base_price ?? 0);
        $discountPrice = $service?->discount_price !== null ? (float) $service->discount_price : null;
        $discountFrom = $service?->discount_from;
        $discountTo = $service?->discount_to;

        if ($this->override_price_mode === self::OVERRIDE_MODE_OVERRIDE) {
            if ($this->override_base_price !== null) {
                $base = (float) $this->override_base_price;
            }

            if ($this->override_discount_price !== null) {
                $discountPrice = (float) $this->override_discount_price;
            }

            $discountFrom = $this->override_discount_from ?? $discountFrom;
            $discountTo = $this->override_discount_to ?? $discountTo;
        }

        $isDiscountActive = $discountPrice !== null;

        if ($isDiscountActive && $discountFrom) {
            $isDiscountActive = $now->gte($discountFrom);
        }
        if ($isDiscountActive && $discountTo) {
            $isDiscountActive = $now->lte($discountTo);
        }

        if ($isDiscountActive && $discountPrice !== null) {
            return max(0.0, (float) $discountPrice);
        }

        return max(0.0, $base);
    }
}
