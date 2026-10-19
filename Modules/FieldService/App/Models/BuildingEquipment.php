<?php

namespace Modules\FieldService\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuildingEquipment extends Model
{
    use HasFactory;

    protected $table = 'field_service_building_equipments';

    protected $fillable = [
        'building_id',
        'equipment_type',
        'title',
        'brand_model',
        'serial_number',
        'installation_year',
        'health_status',
        'last_serviced_at',
        'next_service_due',
        'specifications',
        'notes',
    ];

    protected $casts = [
        'last_serviced_at' => 'datetime',
        'next_service_due' => 'datetime',
        'specifications'   => 'array',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'building_id');
    }

    public function getEquipmentTypeLabelAttribute(): string
    {
        return match ($this->equipment_type) {
            'boiler_room'        => 'موتورخانه مرکزی و مشعل',
            'absorption_chiller' => 'چیلر جذبی',
            'compression_chiller'=> 'چیلر تراکمی',
            'chiller'            => 'چیلر و تهویه مرکزی',
            'booster_pump'       => 'بوستر پمپ آبرسانی / آتش‌نشانی',
            'elevator'           => 'آسانسور و بالابر',
            'generator'          => 'دیزل ژنراتور اضطراری',
            'package_central'    => 'پکیج مرکزی / سیستم گرمایشی',
            'vrf'                => 'سیستم اسپلیت مرکزی (VRF)',
            default              => 'تاسیسات عمومی',
        };
    }

    public function getHealthStatusLabelAttribute(): string
    {
        return match ($this->health_status) {
            'good'             => 'سالم و فعال',
            'needs_inspection' => 'نیازمند بررسی دوره‌ای',
            'critical'         => 'بحرانی / نیازمند تعمیر فوری',
            default            => 'نامشخص',
        };
    }

    public function getHealthBadgeClassAttribute(): string
    {
        return match ($this->health_status) {
            'good'             => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300',
            'needs_inspection' => 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300',
            'critical'         => 'bg-rose-100 text-rose-800 dark:bg-rose-500/15 dark:text-rose-300',
            default            => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        };
    }
}
