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
        'stats'      => 'array',
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
        'province',
        'city',
        'insurances',
        'visibility',
        'stats',
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

    /**
     * Get specialties as array of strings
     */
    public function getSpecialtiesListAttribute(): array
    {
        $val = $this->attributes['specialty'] ?? null;
        if (empty($val)) {
            return [];
        }
        if (is_array($val)) {
            return array_values(array_filter(array_map('trim', $val)));
        }
        if (is_string($val)) {
            $decoded = json_decode($val, true);
            if (is_array($decoded)) {
                return array_values(array_filter(array_map('trim', $decoded)));
            }
            return [trim($val)];
        }
        return [];
    }

    /**
     * Get education items as array of strings
     */
    public function getEducationListAttribute(): array
    {
        $val = $this->attributes['education'] ?? null;
        if (empty($val)) {
            return [];
        }
        if (is_array($val)) {
            return array_values(array_filter(array_map('trim', $val)));
        }
        if (is_string($val)) {
            $decoded = json_decode($val, true);
            if (is_array($decoded)) {
                return array_values(array_filter(array_map('trim', $decoded)));
            }
            return [trim($val)];
        }
        return [];
    }

    /**
     * Get specialties formatted as readable text
     */
    public function getSpecialtyTextAttribute(): string
    {
        return implode('، ', $this->specialties_list);
    }

    /**
     * Get education formatted as readable text
     */
    public function getEducationTextAttribute(): string
    {
        return implode('، ', $this->education_list);
    }

    /**
     * Get numeric years of experience
     */
    public function getExperienceYearsAttribute(): ?int
    {
        $val = $this->attributes['experience'] ?? null;
        if ($val === null || $val === '') {
            return null;
        }

        // Convert Persian/Arabic digits to English
        $en = str_replace(
            ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹','٠','١','٢','٣','٤','٥','٦','٧','٨','٩'],
            ['0','1','2','3','4','5','6','7','8','9','0','1','2','3','4','5','6','7','8','9'],
            (string)$val
        );

        if (preg_match('/\d+/', $en, $matches)) {
            return (int)$matches[0];
        }

        return is_numeric($en) ? (int)$en : null;
    }

    /**
     * Get formatted experience text with 'سال سابقه'
     */
    public function getExperienceTextAttribute(): ?string
    {
        $years = $this->experience_years;
        if ($years !== null && $years > 0) {
            return $years . ' سال سابقه';
        }

        $val = $this->attributes['experience'] ?? null;
        return !empty($val) ? (string)$val : null;
    }

    /**
     * Get stats with clean defaults
     */
    public function getStatsAttribute($value): array
    {
        $defaults = [
            'mode'                      => 'manual', // 'manual' or 'auto'
            'rating'                    => 4.8,
            'reviews_count'             => 0,
            'satisfaction_rate'         => 95,
            'successful_bookings_count' => 0,
            'platform_name'             => '',
            'endorsements_count'        => 0,
            'endorsements_text'         => '',
        ];

        if (empty($value)) {
            return $defaults;
        }

        $decoded = is_string($value) ? json_decode($value, true) : $value;
        return array_merge($defaults, is_array($decoded) ? $decoded : []);
    }

    /**
     * Get effective rating (manual or calculated automatically from reviews in future)
     */
    public function getEffectiveRatingAttribute(): float
    {
        if (($this->stats['mode'] ?? 'manual') === 'auto') {
            // Extensible: If system has customer reviews, calculate dynamically here
        }
        return (float) ($this->stats['rating'] ?? 5.0);
    }

    /**
     * Get effective reviews count
     */
    public function getEffectiveReviewsCountAttribute(): int
    {
        if (($this->stats['mode'] ?? 'manual') === 'auto') {
            // Extensible: If reviews table exists, return count
        }
        return (int) ($this->stats['reviews_count'] ?? 0);
    }

    /**
     * Get effective satisfaction percentage
     */
    public function getEffectiveSatisfactionRateAttribute(): int
    {
        if (($this->stats['mode'] ?? 'manual') === 'auto') {
            // Extensible: Calculate percentage of positive reviews
        }
        return (int) ($this->stats['satisfaction_rate'] ?? 95);
    }

    /**
     * Get effective successful bookings count
     */
    public function getEffectiveSuccessfulBookingsCountAttribute(): int
    {
        if (($this->stats['mode'] ?? 'manual') === 'auto') {
            // Extensible: Query completed appointments for this provider
        }
        return (int) ($this->stats['successful_bookings_count'] ?? 0);
    }

    /**
     * Get effective endorsements count
     */
    public function getEffectiveEndorsementsCountAttribute(): int
    {
        return (int) ($this->stats['endorsements_count'] ?? 0);
    }

    /**
     * Get effective endorsements display text
     */
    public function getEffectiveEndorsementsTextAttribute(): string
    {
        $custom = trim($this->stats['endorsements_text'] ?? '');
        if ($custom !== '') {
            return $custom;
        }
        $count = $this->effective_endorsements_count;
        return $count > 0 ? "{$count} پزشک ایشان را تایید کرده‌اند." : '';
    }

    /**
     * Get effective platform name
     */
    public function getEffectivePlatformNameAttribute(): string
    {
        $custom = trim($this->stats['platform_name'] ?? '');
        return $custom !== '' ? $custom : (config('app.name') ?: 'سامانه');
    }
}
