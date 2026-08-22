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
        $decoded = is_string($value) ? json_decode($value, true) : $value;
        if (!is_array($decoded)) return [];

        return array_values($decoded);
    }

    public function getOptionLabelsAttribute(): array
    {
        $options = $this->options;
        if (!is_array($options)) return [];

        return array_map(function ($opt) {
            if (is_array($opt)) {
                return $opt['label'] ?? ($opt['title'] ?? ($opt['name'] ?? ''));
            }
            return (string) $opt;
        }, $options);
    }

    public function getOptionPrice(mixed $optionLabel, int|float $basePrice = 0): float
    {
        if (!$this->has_pricing) {
            return 0;
        }

        $options = $this->options;
        if (is_array($options)) {
            foreach ($options as $opt) {
                if (is_array($opt)) {
                    $lbl = $opt['label'] ?? ($opt['title'] ?? ($opt['name'] ?? ''));
                    if ((string)$lbl === (string)$optionLabel) {
                        $priceType = $opt['pricing_type'] ?? ($this->pricing_type ?? 'fixed');
                        $optAmount = (float)($opt['price'] ?? ($opt['pricing_amount'] ?? 0));
                        if ($optAmount > 0) {
                            return $priceType === 'percentage'
                                ? round($basePrice * ($optAmount / 100))
                                : $optAmount;
                        }
                    }
                }
            }
        }

        if ($this->pricing_type === 'percentage') {
            return round($basePrice * ((float)($this->pricing_amount ?? 0) / 100));
        }

        return (float)($this->pricing_amount ?? 0);
    }

    public function calculatePriceImpact(mixed $value, int $basePrice = 0): int
    {
        if (! $this->has_pricing) return 0;

        if ($this->type === 'multiselect' && is_array($value)) {
            if (empty($value)) return 0;
            $total = 0;
            foreach ($value as $opt) {
                $total += (int) round($this->getOptionPrice($opt, $basePrice));
            }
            return $total;
        }

        if (in_array($this->type, ['select', 'radio']) && is_string($value) && $value !== '') {
            return (int) round($this->getOptionPrice($value, $basePrice));
        }

        $checked = is_array($value) ? count($value) > 0 : (bool) $value;
        if (! $checked) return 0;

        if ($this->pricing_type === 'fixed') {
            return (int) ($this->pricing_amount ?? 0);
        }

        if ($this->pricing_type === 'percentage') {
            return (int) round($basePrice * ($this->pricing_amount ?? 0) / 100);
        }

        return 0;
    }
}
