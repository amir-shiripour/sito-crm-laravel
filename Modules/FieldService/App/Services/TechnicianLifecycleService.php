<?php

namespace Modules\FieldService\App\Services;

use App\Helpers\ProvinceCity;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\FieldService\App\Enums\TechnicianStatus;
use Modules\FieldService\App\Enums\TechnicianTier;
use Modules\FieldService\App\Models\Category;
use Modules\FieldService\App\Models\Technician;
use Modules\FieldService\App\Models\TechnicianSkill;
use Spatie\Permission\Models\Role;

class TechnicianLifecycleService
{
    /**
     * Create or update a Technician record for a given User.
     * Idempotent and safe to run multiple times.
     */
    public static function createOrUpdateTechnicianForUser(User $user, array $data = []): Technician
    {
        // 1. Resolve names
        $nameParts = explode(' ', trim($user->name ?? ''), 2);
        $firstName = !empty($data['first_name']) ? trim($data['first_name']) : ($nameParts[0] ?? 'تکنسین');
        $lastName  = !empty($data['last_name']) ? trim($data['last_name']) : ($nameParts[1] ?? 'فنی');
        $mobile    = !empty($data['mobile']) ? trim($data['mobile']) : ($user->mobile ?? '');

        // 2. Province and City normalization using Core App\Helpers\ProvinceCity
        $allProvinces = ProvinceCity::getProvinces();
        $rawProvince = $data['province'] ?? null;
        $rawCity     = $data['city'] ?? null;

        if (!empty($rawProvince) && in_array($rawProvince, $allProvinces, true)) {
            $province = $rawProvince;
        } else {
            $province = 'مازندران';
        }

        $citiesForProvince = ProvinceCity::getCities($province);
        if (!empty($rawCity) && in_array($rawCity, $citiesForProvince, true)) {
            $city = $rawCity;
        } elseif ($province === 'مازندران' && in_array('بابل', $citiesForProvince, true)) {
            $city = 'بابل';
        } else {
            $city = $citiesForProvince[0] ?? ($rawCity ?: 'بابل');
        }

        // 3. Status and Tier resolution
        $status = TechnicianStatus::ACTIVE;
        if (isset($data['status'])) {
            if ($data['status'] instanceof TechnicianStatus) {
                $status = $data['status'];
            } else {
                $status = TechnicianStatus::tryFrom($data['status']) ?? TechnicianStatus::ACTIVE;
            }
        }

        $tier = TechnicianTier::SILVER;
        if (isset($data['tier'])) {
            if ($data['tier'] instanceof TechnicianTier) {
                $tier = $data['tier'];
            } else {
                $tier = TechnicianTier::tryFrom($data['tier']) ?? TechnicianTier::SILVER;
            }
        }

        // 4. Meta packaging (custom uploaded files, license, etc.)
        $meta = $data['meta'] ?? [];
        if (!empty($data['national_card_image'])) {
            $meta['national_card_image'] = $data['national_card_image'];
        }
        if (!empty($data['certificate_file'])) {
            $meta['certificate_file'] = $data['certificate_file'];
        }

        // 5. Find existing Technician by user_id or mobile (including soft-deleted records)
        $technician = null;
        if (!empty($user->id)) {
            $technician = Technician::withTrashed()->where('user_id', $user->id)->first();
        }
        if (!$technician && !empty($mobile)) {
            $technician = Technician::withTrashed()->where('mobile', $mobile)->first();
        }

        $attributes = [
            'first_name'            => $firstName,
            'last_name'             => $lastName,
            'mobile'                => $mobile,
            'national_code'         => $data['national_code'] ?? ($technician?->national_code),
            'shaba_number'          => $data['shaba_number'] ?? ($technician?->shaba_number),
            'card_number'           => $data['card_number'] ?? ($technician?->card_number),
            'province'              => $province,
            'city'                  => $city,
            'address'               => $data['address'] ?? ($technician?->address),
            'lat'                   => isset($data['lat']) ? (float)$data['lat'] : ($technician?->lat),
            'lng'                   => isset($data['lng']) ? (float)$data['lng'] : ($technician?->lng),
            'operational_radius_km' => isset($data['operational_radius_km']) ? (int)$data['operational_radius_km'] : ($technician?->operational_radius_km ?? (int) \Modules\FieldService\App\Models\FieldServiceSetting::get('default_technician_radius_km', 25)),
            'status'                => $status,
            'tier'                  => $tier,
            'verified_at'           => $status === TechnicianStatus::ACTIVE ? now() : ($technician?->verified_at),
            'meta'                  => !empty($meta) ? array_merge($technician?->meta ?? [], $meta) : $technician?->meta,
        ];

        if (!$technician) {
            $attributes['user_id'] = $user->id;
            $technician = Technician::create($attributes);
            Log::info("TechnicianLifecycleService: Created Technician #{$technician->id} for User #{$user->id}");
        } else {
            $isReassigned = $technician->trashed()
                || empty($technician->user_id)
                || !User::where('id', $technician->user_id)->exists()
                || (int) $technician->user_id !== (int) $user->id;

            if ($technician->trashed()) {
                $technician->restore();
            }

            if ($isReassigned) {
                $attributes['user_id'] = $user->id;
                $attributes['verified_at'] = $status === TechnicianStatus::ACTIVE ? now() : null;
                $attributes['meta'] = $data['meta'] ?? [];
                $technician->skills()->delete();
                $technician->forceFill($attributes)->save();
            } else {
                $technician->update(array_filter($attributes, fn($v) => !is_null($v)));
            }
            Log::info("TechnicianLifecycleService: Updated Technician #{$technician->id} for User #{$user->id}");
        }

        // 6. Sync categories / skills
        $categories = $data['categories'] ?? $data['specialties'] ?? [];
        if (!empty($categories)) {
            if (is_string($categories)) {
                $categories = json_decode($categories, true) ?: explode(',', $categories);
            }
            if (is_array($categories)) {
                foreach ($categories as $catId) {
                    $catId = (int)$catId;
                    if ($catId > 0 && Category::where('id', $catId)->exists()) {
                        $skill = TechnicianSkill::firstOrCreate([
                            'technician_id' => $technician->id,
                            'category_id'   => $catId,
                        ], [
                            'is_verified'      => ($status === TechnicianStatus::ACTIVE),
                            'experience_years' => 1,
                        ]);

                        if ($status === TechnicianStatus::ACTIVE && !$skill->is_verified) {
                            $skill->is_verified = true;
                            $skill->save();
                        }
                    }
                }
            }
        }

        // 7. Ensure Spatie role 'technician' is assigned to User
        if (!$user->hasRole('technician')) {
            $user->assignRole('technician');
        }

        return $technician;
    }

    /**
     * Create a technician from operator/admin direct entry.
     * Finds or provisions a matching User account automatically.
     */
    public static function createDirectTechnician(array $data): Technician
    {
        $mobile = trim($data['mobile'] ?? '');
        $firstName = trim($data['first_name'] ?? 'تکنسین');
        $lastName = trim($data['last_name'] ?? 'فنی');
        $fullName = trim("{$firstName} {$lastName}");

        // Find or create User
        $user = null;
        if (!empty($mobile)) {
            $user = User::where('mobile', $mobile)->first();
        }

        if (!$user) {
            $dummyEmail = 'tech_' . ($mobile ?: Str::random(8)) . '@crm.local';
            $user = User::create([
                'name'            => $fullName,
                'email'           => $data['email'] ?? $dummyEmail,
                'mobile'          => $mobile,
                'password'        => Hash::make(Str::random(24)),
                'password_set_at' => now(),
            ]);
        } else {
            if (empty($user->name) || $user->name === 'کاربر') {
                $user->update(['name' => $fullName]);
            }
        }

        // Make sure technician role exists and assign
        $role = Role::firstOrCreate(['name' => 'technician', 'guard_name' => 'web'], [
            'display_name' => 'متخصص و تکنسین فنی',
        ]);
        if (!$user->hasRole('technician')) {
            $user->assignRole($role);
        }

        return self::createOrUpdateTechnicianForUser($user, $data);
    }

    /**
     * Trigger ContractForge auto-rules when a technician is approved/activated.
     */
    public static function triggerApprovalContract(Technician $technician, ?string $previousStatus = null): int
    {
        if (
            $technician->status !== TechnicianStatus::ACTIVE
            || ! class_exists(\Modules\ContractForge\Services\ContractModuleHelper::class)
            || ! class_exists(\Modules\ContractForge\Services\ContractEngine::class)
            || ! \Modules\ContractForge\Services\ContractModuleHelper::isModuleActive('ContractForge')
        ) {
            return 0;
        }

        try {
            return \Modules\ContractForge\Services\ContractEngine::autoTrigger(
                'field_service_technician',
                $technician,
                'status_changed',
                $previousStatus
            );
        } catch (\Throwable $e) {
            Log::warning('TechnicianLifecycleService::triggerApprovalContract error: ' . $e->getMessage());
            return 0;
        }
    }
}
