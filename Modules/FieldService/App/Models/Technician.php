<?php

namespace Modules\FieldService\App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
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

    public function contracts(): MorphMany
    {
        return $this->morphMany(\Modules\ContractForge\App\Models\Contract::class, 'contractable');
    }

    /**
     * Check whether this technician has an active (unsigned) contract awaiting confirmation.
     */
    public function hasPendingContract(): bool
    {
        if (
            ! class_exists(\Modules\ContractForge\App\Models\Contract::class)
            || ! class_exists(\Modules\ContractForge\Services\ContractModuleHelper::class)
            || ! \Modules\ContractForge\Services\ContractModuleHelper::isModuleActive('ContractForge')
        ) {
            return false;
        }

        return $this->contracts()->where('status', 'active')->exists();
    }

    public function getContractEntityType(): string
    {
        return 'field_service_technician';
    }

    public function getContractTitle(): string
    {
        return $this->full_name ?: ('تکنسین #' . $this->id);
    }

    public function getContractClientId(): ?int
    {
        return null;
    }

    public function getContractTokens(): array
    {
        $skills = $this->categories()->with(['section', 'group.section'])->get();

        $skillNames = [];
        $rowsHtml = '';
        foreach ($skills as $idx => $cat) {
            $secName = $cat->group?->section?->name ?? $cat->section?->name ?? '-';
            $grpName = $cat->group?->name ?? '-';
            $skillNames[] = $cat->name;
            $num = $idx + 1;
            $rowsHtml .= "<tr><td>{$num}</td><td>" . e($secName) . "</td><td>" . e($grpName) . "</td><td>" . e($cat->name) . "</td></tr>";
        }

        $skillsTableHtml = $skills->isNotEmpty()
            ? "<table class='contract-table'><thead><tr><th>ردیف</th><th>بخش</th><th>دسته</th><th>خدمت / تخصص</th></tr></thead><tbody>{$rowsHtml}</tbody></table>"
            : '<p>تخصصی ثبت نشده است.</p>';

        $todayJalali = function_exists('jdate')
            ? jdate()->format('Y/m/d')
            : (class_exists(\Morilog\Jalali\Jalalian::class) ? \Morilog\Jalali\Jalalian::now()->format('Y/m/d') : now()->format('Y-m-d'));

        $verifiedAt = $this->verified_at
            ? (function_exists('jdate') ? jdate($this->verified_at)->format('Y/m/d H:i') : $this->verified_at->format('Y-m-d H:i'))
            : '-';

        $platformName = class_exists(FieldServiceSetting::class)
            ? FieldServiceSetting::getPlatformName()
            : config('app.name', 'CRM');

        $tokens = [
            'technician_id'           => (string) $this->id,
            'technician_name'         => $this->full_name,
            'technician_first_name'   => (string) ($this->first_name ?? ''),
            'technician_last_name'    => (string) ($this->last_name ?? ''),
            'technician_mobile'       => (string) ($this->mobile ?? ''),
            'technician_radius_km'    => (string) ($this->operational_radius_km ?? 0),
            'technician_tier'         => $this->tier?->label() ?? (string) $this->tier,
            'technician_status'       => $this->status?->label() ?? (string) $this->status,
            'technician_verified_at'  => $verifiedAt,
            'technician_skills_list'  => !empty($skillNames) ? implode('، ', $skillNames) : '-',
            'technician_skills_table' => $skillsTableHtml,
            'platform_name'           => $platformName,
            'today_jalali'            => $todayJalali,
        ];

        // Dynamically resolve all CustomUserField entries defined for 'technician' role
        if (class_exists(\App\Models\CustomUserField::class) && class_exists(\Modules\FieldService\App\Support\TechnicianCustomFieldsHelper::class)) {
            $customFields = \App\Models\CustomUserField::where('role_name', 'technician')->orderBy('id')->get();
            if ($customFields->isNotEmpty()) {
                $values = \Modules\FieldService\App\Support\TechnicianCustomFieldsHelper::loadExistingValues($this->user, $this, $customFields);
                $reservedKeys = array_merge(array_keys($tokens), ['contract_number']);

                foreach ($customFields as $field) {
                    $key = in_array($field->field_name, $reservedKeys, true)
                        ? 'cf_' . $field->field_name
                        : $field->field_name;
                    $val = $values[$field->field_name] ?? null;

                    if (strtolower($field->field_type ?? '') === 'file') {
                        $tokens[$key] = !empty($val) ? 'پیوست شده' : '-';
                    } elseif (is_array($val)) {
                        $tokens[$key] = !empty($val) ? implode('، ', $val) : '-';
                    } else {
                        $tokens[$key] = ($val !== null && $val !== '') ? (string) $val : '';
                    }
                }
            }
        }

        return $tokens;
    }
}
