<?php

namespace Modules\Booking\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Booking\Entities\BookingAvailabilityRule;
use Modules\Booking\Entities\BookingService;
use Modules\Booking\Entities\BookingSetting;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Arr;


class ServiceAvailabilityController extends Controller
{
    public function edit(BookingService $service)
    {
        $user     = Auth::user();
        $settings = BookingSetting::current();
        $adminOwnerIds = $this->getAdminOwnerIds();

        if (! $this->canEditServiceForUser($user, $service, $adminOwnerIds, $settings)) {
            abort(403, 'You are not allowed to manage availability for this service.');
        }

        $rules = BookingAvailabilityRule::query()
            ->where('scope_type', BookingAvailabilityRule::SCOPE_SERVICE)
            ->where('scope_id', $service->id)
            ->get()
            ->keyBy('weekday');

        return view('booking::user.services.availability', compact('service', 'rules'));
    }

    public function update(Request $request, BookingService $service)
    {
        // --- normalize times before validation ---
        $rulesInput = (array) $request->input('rules', []);

        foreach ($rulesInput as $day => $row) {
            foreach (['work_start_local', 'work_end_local'] as $f) {
                if (array_key_exists($f, $row)) {
                    $v = trim((string) $row[$f]);
                    $rulesInput[$day][$f] = ($v === '') ? null : substr($v, 0, 5); // "09:00:00" -> "09:00"
                }
            }

            if (isset($row['breaks']) && is_array($row['breaks'])) {
                foreach ($row['breaks'] as $i => $br) {
                    foreach (['start_local', 'end_local'] as $bf) {
                        if (array_key_exists($bf, $br)) {
                            $v = trim((string) $br[$bf]);
                            $rulesInput[$day]['breaks'][$i][$bf] = ($v === '') ? null : substr($v, 0, 5);
                        }
                    }
                }
            }
        }

        $request->merge(['rules' => $rulesInput]);
// --- end normalize ---

        $data = $request->validate([
            'rules' => ['required', 'array'],

            'rules.*.weekday' => ['nullable', 'integer', 'min:0', 'max:6'],
            'rules.*.is_closed' => ['required', Rule::in(['0','1'])],
            'rules.*.work_start_local' => ['nullable', 'date_format:H:i'],
            'rules.*.work_end_local'   => ['nullable', 'date_format:H:i'],

            'rules.*.breaks' => ['nullable', 'array'],
            'rules.*.breaks.*.start_local' => ['required_with:rules.*.breaks.*.end_local', 'date_format:H:i'],
            'rules.*.breaks.*.end_local'   => ['required_with:rules.*.breaks.*.start_local', 'date_format:H:i'],

            'rules.*.slot_duration_minutes' => ['nullable', 'integer', 'min:5', 'max:720'],

            // ✅ اجازه 0 (برای نامحدود/اختیاری)
            'rules.*.capacity_per_slot' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'rules.*.capacity_per_day'  => ['nullable', 'integer', 'min:0', 'max:10000'],
        ]);

        $rules = $data['rules'] ?? [];

        foreach ($rules as $weekday => $ruleRow) {
            $weekday = (int) Arr::get($ruleRow, 'weekday', $weekday);
            if ($weekday < 0 || $weekday > 6) {
                continue;
            }

            // نرمال‌سازی فیلدها (مهم: 0 باید حفظ شود)
            $workStart = Arr::get($ruleRow, 'work_start_local');
            $workEnd   = Arr::get($ruleRow, 'work_end_local');

            $slotDur = Arr::get($ruleRow, 'slot_duration_minutes');
            $capSlot = Arr::get($ruleRow, 'capacity_per_slot');
            $capDay  = Arr::get($ruleRow, 'capacity_per_day');

            $slotDur = ($slotDur === '' || $slotDur === null) ? null : (int) $slotDur;
            $capSlot = ($capSlot === '' || $capSlot === null) ? null : (int) $capSlot; // ✅ 0 حفظ می‌شود
            $capDay  = ($capDay === ''  || $capDay === null)  ? null : (int) $capDay;  // ✅ 0 حفظ می‌شود

            $payload = [
                'scope_type' => BookingAvailabilityRule::SCOPE_SERVICE,
                'scope_id' => $service->id,
                'weekday' => $weekday,

                'is_closed' => ((string)Arr::get($ruleRow, 'is_closed', '0') === '1'),

                'work_start_local' => ($workStart === '' ? null : $workStart),
                'work_end_local'   => ($workEnd === '' ? null : $workEnd),

                'breaks_json' => isset($ruleRow['breaks'])
                    ? array_values($ruleRow['breaks'] ?? [])
                    : null,

                'slot_duration_minutes' => $slotDur,
                'capacity_per_slot' => $capSlot,
                'capacity_per_day'  => $capDay,
            ];

            // ✅ allNull فقط با null چک شود، نه falsy (تا 0 باعث حذف نشود)
            $allNull =
                $payload['is_closed'] === false &&
                $payload['work_start_local'] === null &&
                $payload['work_end_local'] === null &&
                $payload['breaks_json'] === null &&
                $payload['slot_duration_minutes'] === null &&
                $payload['capacity_per_slot'] === null &&
                $payload['capacity_per_day'] === null;

            $existing = BookingAvailabilityRule::query()
                ->where('scope_type', BookingAvailabilityRule::SCOPE_SERVICE)
                ->where('scope_id', $service->id)
                ->where('weekday', $weekday)
                ->first();

            if ($allNull) {
                if ($existing) $existing->delete();
                continue;
            }

            BookingAvailabilityRule::query()->updateOrCreate(
                [
                    'scope_type' => BookingAvailabilityRule::SCOPE_SERVICE,
                    'scope_id' => $service->id,
                    'weekday' => $weekday,
                ],
                $payload
            );
        }

        return redirect()
            ->route('user.booking.services.availability.edit', $service)
            ->with('success', 'برنامه زمانی سرویس ذخیره شد.');
    }

    // ------------------------------------------------------------------
    // Helperهای دسترسی (کپی‌شده از ServiceController برای همسانی رفتار)
    // ------------------------------------------------------------------

    protected function isAdminUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return true;
        }

        if ($user->can('booking.manage') || $user->can('booking.services.manage')) {
            return true;
        }

        return false;
    }

    protected function getProviderRoleIds(BookingSetting $settings): array
    {
        return array_map('intval', (array)($settings->allowed_roles ?? []));
    }

    protected function userIsProvider(?User $user, BookingSetting $settings): bool
    {
        if (! $user) {
            return false;
        }

        $providerRoleIds = $this->getProviderRoleIds($settings);
        if (empty($providerRoleIds)) {
            return false;
        }

        $userRoleIds = $user->roles()->pluck('id')->map(fn ($v) => (int) $v)->all();

        return count(array_intersect($providerRoleIds, $userRoleIds)) > 0;
    }

    protected function getAdminOwnerIds(): array
    {
        $roleIds = Role::query()
            ->whereIn('name', ['super-admin', 'admin'])
            ->pluck('id')
            ->all();

        if (empty($roleIds)) {
            return [];
        }

        return DB::table('model_has_roles')
            ->whereIn('role_id', $roleIds)
            ->where('model_type', User::class)
            ->pluck('model_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    protected function serviceIsPublic(BookingService $service, array $adminOwnerIds): bool
    {
        if ($service->owner_user_id === null) {
            return true;
        }

        return in_array((int) $service->owner_user_id, $adminOwnerIds, true);
    }

    /**
     * همون منطق ServiceController::canEditServiceForUser
     */
    protected function canEditServiceForUser(?User $user, BookingService $service, array $adminOwnerIds, BookingSetting $settings): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->isAdminUser($user)) {
            return true;
        }

        $isProvider = $this->userIsProvider($user, $settings);
        if (! $isProvider) {
            return false;
        }

        // مالک سرویس (در صورت فعال بودن امکان ساخت سرویس)
        if ($settings->allow_role_service_creation && (int) $service->owner_user_id === (int) $user->id) {
            return true;
        }

        // سرویس عمومی و قابل شخصی‌سازی توسط Provider
        if ($this->serviceIsPublic($service, $adminOwnerIds) && $service->provider_can_customize) {
            return true;
        }

        return false;
    }
}
