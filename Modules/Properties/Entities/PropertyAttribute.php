<?php

namespace Modules\Properties\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PropertyAttribute extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'section', 'options', 'sort_order', 'is_active'];

    protected $casts = [
        'options' => 'array',
        'is_active' => 'boolean',
    ];

    public function values()
    {
        return $this->hasMany(PropertyAttributeValue::class, 'attribute_id');
    }
}
