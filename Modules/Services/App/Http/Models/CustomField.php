<?php

namespace Modules\Services\App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomField extends Model
{
    use SoftDeletes;

    protected $table = 'services_custom_fields';
    protected $fillable = [
        'label',
        'key',
        'type',
        'options',
        'default_value',
        'is_required',
        'has_pricing',
        'pricing_type',
        'pricing_amount',
        'sort_order',
        'show_in_invoice',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'has_pricing' => 'boolean',
        'show_in_invoice' => 'boolean',
        'pricing_amount' => 'integer',
    ];

    public function fieldable(): MorphTo
    {
        return $this->morphTo();
    }

    public function values(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class);
    }

    public function setOptionsAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['options'] = json_encode($value, JSON_UNESCAPED_UNICODE);
        } else {
            $this->attributes['options'] = $value;
        }
    }

    public function getOptionsAttribute($value)
    {
        if (empty($value)) return [];
        return is_string($value) ? json_decode($value, true) : $value;
    }

    public function calculatePriceImpact(mixed $value, int $basePrice = 0): int
    {
        if (! $this->has_pricing) return 0;

        $checked = is_array($value) ? count($value) > 0 : (bool) $value;
        if (! $checked) return 0;

        if ($this->pricing_type === 'fixed') {
            return $this->pricing_amount;
        }

        if ($this->pricing_type === 'percentage') {
            return (int) round($basePrice * $this->pricing_amount / 100);
        }

        return 0;
    }
}
