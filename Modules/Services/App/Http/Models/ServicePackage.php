<?php

namespace Modules\Services\App\Http\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServicePackage extends Model
{
    use SoftDeletes;

    protected $table = 'service_packages';

    protected $fillable = [
        'name',
        'code',
        'description',
        'total_amount',
        'discount_type',
        'discount_value',
        'final_price',
        'status',
    ];

    protected $casts = [
        'total_amount' => 'integer',
        'discount_value' => 'integer',
        'final_price' => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ServicePackageItem::class, 'package_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
