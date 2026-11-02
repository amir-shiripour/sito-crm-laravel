<?php

namespace Modules\Properties\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PropertyAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'section',
        'icon',
        'options',
        'sort_order',
        'is_active',
        'is_filterable',
        'is_range_filter'
    ];

    protected $casts = [
        'options' => 'array',
        'is_active' => 'boolean',
        'is_filterable' => 'boolean',
        'is_range_filter' => 'boolean',
    ];

    public function values()
    {
        return $this->hasMany(PropertyAttributeValue::class, 'attribute_id');
    }

    /**
     * Helper to render the attribute icon
     *
     * @param string $fallback
     * @return string
     */
    public function renderIcon(string $fallback = '✨'): string
    {
        return !empty($this->icon) ? (string) $this->icon : $fallback;
    }
}
