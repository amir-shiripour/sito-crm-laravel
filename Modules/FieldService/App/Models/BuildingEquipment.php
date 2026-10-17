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
}
