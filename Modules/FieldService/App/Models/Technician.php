<?php

namespace Modules\FieldService\App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\FieldService\App\Enums\TechnicianStatus;
use Modules\FieldService\App\Enums\TechnicianTier;
use Modules\Wallet\App\Traits\HasWallet;

class Technician extends Model
{
    use HasFactory, SoftDeletes, HasWallet;

    protected $table = 'field_service_technicians';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'mobile',
        'national_code',
        'shaba_number',
        'card_number',
        'avatar',
        'province',
        'city',
        'address',
        'lat',
        'lng',
        'operational_radius_km',
        'is_online',
        'is_busy',
        'status',
        'tier',
        'rating_avg',
        'rating_count',
        'completed_orders_count',
        'acceptance_rate',
        'last_active_at',
        'verified_at',
        'meta',
    ];

    protected $casts = [
        'is_online'       => 'boolean',
        'is_busy'         => 'boolean',
        'status'          => TechnicianStatus::class,
        'tier'            => TechnicianTier::class,
        'lat'             => 'float',
        'lng'             => 'float',
        'rating_avg'      => 'float',
        'acceptance_rate' => 'float',
        'last_active_at'  => 'datetime',
        'verified_at'     => 'datetime',
        'meta'            => 'array',
    ];

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function skills(): HasMany
    {
        return $this->hasMany(TechnicianSkill::class, 'technician_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'field_service_technician_skills',
            'technician_id',
            'category_id'
        )->withPivot(['is_verified', 'certificate_number', 'experience_years'])->withTimestamps();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class, 'technician_id');
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(PayaSettlement::class, 'technician_id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(ServiceRating::class, 'technician_id');
    }

    public function backupRequestsAsPrimary(): HasMany
    {
        return $this->hasMany(BackupRequest::class, 'primary_technician_id');
    }

    public function backupRequestsAsAssistant(): HasMany
    {
        return $this->hasMany(BackupRequest::class, 'assistant_technician_id');
    }

    /**
     * Calculate approximate distance to coordinates in kilometers using Haversine formula.
     */
    public function distanceTo(?float $targetLat, ?float $targetLng): ?float
    {
        if (!$this->lat || !$this->lng || !$targetLat || !$targetLng) {
            return null;
        }

        $earthRadius = 6371; // km
        $dLat = deg2rad($targetLat - $this->lat);
        $dLon = deg2rad($targetLng - $this->lng);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($this->lat)) * cos(deg2rad($targetLat)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earthRadius * $c, 2);
    }
}
