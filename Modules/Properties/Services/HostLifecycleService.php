<?php

namespace Modules\Properties\Services;

use App\Models\User;
use Modules\Properties\Entities\PropertyHost;
use Modules\Properties\Entities\PropertyOwner;
use Modules\Properties\Entities\PropertySetting;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class HostLifecycleService
{
    /**
     * Create or attach a PropertyHost and PropertyOwner profile for a User.
     * This is idempotent and can be safely called multiple times.
     */
    public static function createOrUpdateHostForUser(User $user, array $additionalData = []): PropertyHost
    {
        // 1. Check if user already has a PropertyHost record
        $host = PropertyHost::where('user_id', $user->id)->first();

        // 2. Resolve or create PropertyOwner for CRM sync
        $phone = $additionalData['phone'] ?? $user->mobile ?? $user->phone;
        $displayName = $additionalData['display_name'] ?? $user->name ?? 'میزبان اقامتگاه';

        $owner = null;
        if (!empty($user->id)) {
            $owner = PropertyOwner::where('user_id', $user->id)->first();
        }
        if (!$owner && !empty($phone)) {
            $owner = PropertyOwner::where('phone', $phone)->first();
        }

        if (!$owner) {
            $nameParts = explode(' ', trim($displayName), 2);
            $firstName = $nameParts[0] ?? $displayName;
            $lastName = $nameParts[1] ?? ' ';

            $owner = PropertyOwner::create([
                'user_id'    => $user->id,
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'phone'      => $phone,
                'created_by' => $user->id,
            ]);
        } elseif (!$owner->user_id) {
            $owner->update(['user_id' => $user->id]);
        }

        // 3. Determine host status based on property settings
        $autoApprove = (bool) PropertySetting::get('rental_host_auto_approve', 0);
        $initialStatus = $autoApprove ? 'active' : 'pending';

        if (!$host) {
            // Generate clean unique slug
            $baseSlug = Str::slug($displayName) ?: 'host';
            $slug = $baseSlug . '-' . $user->id;

            $host = PropertyHost::create([
                'user_id'            => $user->id,
                'owner_id'           => $owner?->id,
                'display_name'       => $displayName,
                'slug'               => $slug,
                'phone'              => $phone,
                'about'              => $additionalData['about'] ?? null,
                'shaba_number'       => $additionalData['shaba_number'] ?? null,
                'bank_name'          => $additionalData['bank_name'] ?? null,
                'account_owner_name' => $additionalData['account_owner_name'] ?? null,
                'national_code'      => $additionalData['national_code'] ?? null,
                'status'             => $initialStatus,
                'kyc_status'         => !empty($additionalData['national_code']) ? 'pending' : 'not_submitted',
            ]);

            Log::info("PropertyHost created automatically for User #{$user->id} with status: {$initialStatus}");
        } else {
            // Update phone or owner if missing
            $updates = [];
            if (empty($host->owner_id) && $owner) {
                $updates['owner_id'] = $owner->id;
            }
            if (empty($host->phone) && !empty($phone)) {
                $updates['phone'] = $phone;
            }
            if (!empty($updates)) {
                $host->update($updates);
            }
        }

        // 4. Ensure role 'property_host' is assigned to the user
        if (!$user->hasRole('property_host')) {
            $user->assignRole('property_host');
        }

        return $host;
    }
}
