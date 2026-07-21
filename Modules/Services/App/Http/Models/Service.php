<?php

namespace Modules\Services\App\Http\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Modules\Settings\Entities\Setting;

class Service extends Model
{
    use SoftDeletes;

    protected $table = 'services';

    protected $fillable = [
        'name',
        'code',
        'category_id',
        'template_id',
        'description',
        'status_id',
        'base_price',
        'setup_fee',
        'has_unit_pricing',
        'unit_name',
        'unit_price',
        'renewal_prices',
        'billing_type',
        'recurring_period',
        'custom_period_days',
        'renewal_reminder_days',
        'auto_renewal',
        'meta',
        'sort_order',
    ];

    protected $casts = [
        'base_price' => 'integer',
        'setup_fee' => 'integer',
        'has_unit_pricing' => 'boolean',
        'unit_price' => 'integer',
        'renewal_prices' => 'array',
        'auto_renewal' => 'boolean',
        'meta' => 'array',
    ];


    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ServiceTemplate::class, 'template_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function customFields(): MorphMany
    {
        return $this->morphMany(CustomField::class, 'fieldable')->orderBy('sort_order');
    }

    public function projects(): Service|Builder|HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function invoices(): Service|Builder|HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function isActive(): bool
    {
        return $this->status?->name === 'فعال';
    }

    public function isInactive(): bool
    {
        return $this->status?->name === 'غیر فعال';
    }

    public function finalPrice(): int
    {
        return (int) round($this->base_price);
    }

    public function getRenewalPriceFor(string $period): int
    {
        if (isset($this->renewal_prices[$period]) && $this->renewal_prices[$period] !== null) {
            return (int)$this->renewal_prices[$period];
        }

        return match ($period) {
            'monthly' => $this->base_price,
            'quarterly' => $this->base_price * 3,
            'semi_annually' => $this->base_price * 6,
            'annually' => $this->base_price * 12,
            'biennially' => $this->base_price * 24,
            'triennially' => $this->base_price * 36,
            default => 0,
        };
    }

    public function scopeActive($query)
    {
        return $query->whereHas('status', function ($q) {
            $q->where('name', 'فعال');
        });
    }
}
