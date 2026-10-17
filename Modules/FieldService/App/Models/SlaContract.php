<?php

namespace Modules\FieldService\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlaContract extends Model
{
    use HasFactory;

    protected $table = 'field_service_sla_contracts';

    protected $fillable = [
        'building_id',
        'contract_number',
        'title',
        'tier',
        'start_date',
        'end_date',
        'monthly_fee',
        'emergency_response_hours',
        'periodic_visits_per_month',
        'status',
        'terms',
    ];

    protected $casts = [
        'start_date'                => 'date',
        'end_date'                  => 'date',
        'monthly_fee'               => 'float',
        'emergency_response_hours'  => 'integer',
        'periodic_visits_per_month' => 'integer',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'building_id');
    }
}
