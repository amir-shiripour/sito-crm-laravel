<?php

namespace Modules\Booking\App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DoctorProfile extends Model
{
    protected $casts = [
        'insurances' => 'array',
        'visibility' => 'array',
    ];

    protected $fillable = [
        'user_id',
        'about_me',
        'education',
        'clinic_name',
        'medical_system_number',
        'experience',
        'specialty',
        'clinic_address',
        'insurances',
        'visibility',
    ];

    /**
     * Check if a section should be shown on the public profile.
     * Defaults to true if not explicitly set.
     */
    public function isVisible(string $section): bool
    {
        $vis = $this->visibility ?? [];
        return (bool) ($vis[$section] ?? false);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(DoctorMedia::class, 'user_id', 'user_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(DoctorMedia::class, 'user_id', 'user_id')
            ->where('type', 'photo');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(DoctorMedia::class, 'user_id', 'user_id')
            ->where('type', 'video');
    }
    public function visible($field)
    {
        return $this->isVisible($field) ? $this->{$field} : null;
    }
}
