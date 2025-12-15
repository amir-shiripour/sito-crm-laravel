<?php

namespace Modules\Booking\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class BookingService extends Model
{
    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_INACTIVE = 'INACTIVE';

    public const ONLINE_MODE_INHERIT = 'INHERIT';
    public const ONLINE_MODE_FORCE_ON = 'FORCE_ON';
    public const ONLINE_MODE_FORCE_OFF = 'FORCE_OFF';

    public const PAYMENT_MODE_NONE = 'NONE';
    public const PAYMENT_MODE_OPTIONAL = 'OPTIONAL';
    public const PAYMENT_MODE_REQUIRED = 'REQUIRED';

    public const PAYMENT_AMOUNT_FULL = 'FULL';
    public const PAYMENT_AMOUNT_DEPOSIT = 'DEPOSIT';
    public const PAYMENT_AMOUNT_FIXED = 'FIXED_AMOUNT';

    protected $table = 'booking_services';

    protected $fillable = [
        'owner_user_id',
        'name',
        'slug',
        'status',
        'base_price',
        'discount_price',
        'discount_from',
        'discount_to',
        'category_id',
        'online_booking_mode',
        'payment_mode',
        'payment_amount_type',
        'payment_amount_value',
        'appointment_form_id',
        'client_profile_required_fields',
        'provider_can_customize',
    ];

    protected $casts = [
        'discount_from' => 'datetime',
        'discount_to' => 'datetime',
        'client_profile_required_fields' => 'array',
        'provider_can_customize' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $m) {
            if (!$m->slug) {
                $m->slug = static::makeUniqueSlug($m->name);
            }
        });
    }

    protected static function makeUniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        if (!$base) {
            $base = 'srv-' . Str::lower(Str::random(6));
        }

        $slug = $base;
        $i = 1;
        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BookingCategory::class, 'category_id');
    }

    public function appointmentForm(): BelongsTo
    {
        return $this->belongsTo(BookingForm::class, 'appointment_form_id');
    }

    public function serviceProviders(): HasMany
    {
        return $this->hasMany(BookingServiceProvider::class, 'service_id');
    }

    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\User::class, 'booking_service_providers', 'service_id', 'provider_user_id')
            ->withPivot(['id', 'is_active', 'customization_enabled'])
            ->withTimestamps();
    }
}
