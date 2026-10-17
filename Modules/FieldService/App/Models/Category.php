<?php

namespace Modules\FieldService\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\FieldService\App\Enums\ApplianceType;

class Category extends Model
{
    use HasFactory;

    protected $table = 'field_service_categories';

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'appliance_type',
        'base_labor_fee',
        'warranty_days',
        'is_active',
        'sort_order',
        'description',
    ];

    protected $casts = [
        'appliance_type' => ApplianceType::class,
        'base_labor_fee' => 'float',
        'warranty_days'  => 'integer',
        'is_active'      => 'boolean',
        'sort_order'     => 'integer',
    ];

    public function technicians(): BelongsToMany
    {
        return $this->belongsToMany(
            Technician::class,
            'field_service_technician_skills',
            'category_id',
            'technician_id'
        )->withPivot(['is_verified', 'certificate_number', 'experience_years'])->withTimestamps();
    }

    public function skills(): HasMany
    {
        return $this->hasMany(TechnicianSkill::class, 'category_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class, 'category_id');
    }
}
