<?php

namespace Modules\Services\App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicePackageItem extends Model
{
    protected $table = 'service_package_items';

    protected $fillable = [
        'package_id',
        'service_id',
        'custom_service_name',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'discount_type',
        'discount_value',
        'discount_amount',
        'billing_period',
        'custom_fields',
        'custom_fields_prices',
        'custom_fields_quantities',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'float',
        'unit_price' => 'integer',
        'discount_value' => 'integer',
        'discount_amount' => 'integer',
        'total_price' => 'integer',
        'custom_fields' => 'array',
        'custom_fields_prices' => 'array',
        'custom_fields_quantities' => 'array',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(ServicePackage::class, 'package_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
