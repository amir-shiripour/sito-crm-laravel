<?php

namespace Modules\Properties\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PropertyStatus extends Model
{
    use HasFactory;

    protected $table = 'property_statuses';

    protected $fillable = [
        'key',
        'label',
        'color',
        'is_system',
        'is_active',
        'is_default',
        'show_in_crm',
        'sort_order',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'show_in_crm' => 'boolean',
    ];
}
