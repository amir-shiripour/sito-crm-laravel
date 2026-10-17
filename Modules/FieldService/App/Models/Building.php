<?php

namespace Modules\FieldService\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Clients\Entities\Client;

class Building extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'field_service_buildings';

    protected $fillable = [
        'client_id',
        'name',
        'building_type',
        'units_count',
        'floors_count',
        'manager_name',
        'manager_phone',
        'province',
        'city',
        'address',
        'lat',
        'lng',
        'notes',
    ];

    protected $casts = [
        'units_count'  => 'integer',
        'floors_count' => 'integer',
        'lat'          => 'float',
        'lng'          => 'float',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function equipments(): HasMany
    {
        return $this->hasMany(BuildingEquipment::class, 'building_id');
    }

    public function slaContracts(): HasMany
    {
        return $this->hasMany(SlaContract::class, 'building_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class, 'building_id');
    }

    public function activeSlaContract(): ?SlaContract
    {
        return $this->slaContracts()->where('status', 'active')->latest()->first();
    }
}
