<?php

namespace Modules\Market\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShippingRule extends Model
{
    use HasFactory;

    protected $table = 'market_shipping_rules';

    protected $fillable = [
        'name',
        'conditions',
        'min_grand_total',
        'action_type',
        'action_value',
        'is_active',
    ];

    protected $casts = [
        'conditions' => 'array',
        'is_active' => 'boolean',
    ];
}
