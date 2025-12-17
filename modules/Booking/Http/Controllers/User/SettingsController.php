<?php

namespace Modules\Booking\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Booking\Entities\BookingSetting;
use Modules\Booking\Entities\BookingAvailabilityRule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    public function edit()
    {
        $settings = BookingSetting::current();

        $rules = [];
        for ($weekday = 0; $weekday <= 6; $weekday++) {
            $rule = BookingAvailabilityRule::query()
                ->where('scope_type', BookingAvailabilityRule::SCOPE_GLOBAL)
                ->where('weekday', $weekday)
                ->first();

            if (!$rule) {
                $rule = new BookingAvailabilityRule([
                    'scope_type' => BookingAvailabilityRule::SCOPE_GLOBAL,
                    'scope_id'   => null,
                    'weekday'    => $weekday,
                    'is_closed'  => false,
                    'work_start_local' => null,
                    'work_end_local'   => null,
                    'breaks_json'      => [],
                    'slot_duration_minutes' => null,
                    'capacity_per_slot' => null,
                    'capacity_per_day'  => null,
                ]);
            }
            $rules[$weekday] = $rule;
        }

        $roles = Role::orderBy('name')->get();

        return view('booking::user.settings.edit', compact('settings', 'rules', 'roles'));
    }

    public function update(Request $request)
    {
        $settings = BookingSetting::current();

        // قدیمی‌ها قبل از آپدیت برای محاسبه تفاوت
        $oldAllowedRoles = (array) ($settings->allowed_roles ?? []);

        $data = $request->validate([
            'currency_unit' => ['required', Rule::in(['IRR', 'IRT'])],
            'global_online_booking_enabled' => ['required'],
            'default_slot_duration_minutes' => ['required', 'integer', 'min:5', 'max:720'],
            'default_capacity_per_slot' => ['required', 'integer', 'min:1', 'max:1000'],
            'default_capacity_per_day' => ['nullable', 'integer', 'min:1', 'max:10000'],

            'allow_role_service_creation' => ['required'],
            'allowed_roles' => ['nullable'],

            'category_management_scope' => ['required', Rule::in(['ALL', 'OWN'])],
            'form_management_scope' => ['required', Rule::in(['ALL', 'OWN'])],
            'service_category_selection_scope' => ['required', Rule::in(['ALL', 'OWN'])],
            'service_form_selection_scope' => ['required', Rule::in(['ALL', 'OWN'])],
            'operator_appointment_flow' => ['required', Rule::in(['PROVIDER_FIRST', 'SERVICE_FIRST'])],
        ]);

        $data['global_online_booking_enabled'] = (bool) $data['global_online_booking_enabled'];
        $data['allow_role_service_creation']   = (bool) $data['allow_role_service_creation'];

        // پر کردن فیلدهای ساده
        $settings->fill($data);

        // نرمال‌سازی allowed_roles
        $rolesInput = $request->input('allowed_roles', []);
        if (is_string($rolesInput)) {
            $decoded = json_decode($rolesInput, true);
            $rolesInput = is_array($decoded) ? $decoded : [];
        }
        $rolesInput = array_values(array_filter($rolesInput, fn ($v) => $v !== null && $v !== ''));

        $settings->allowed_roles = $rolesInput;
        $settings->save();

        // ---------------------------
        // مدیریت Permission نقش‌های Provider
        // ---------------------------

        $newAllowedRoles = (array) ($settings->allowed_roles ?? []);

        $old = array_map('strval', $oldAllowedRoles);
        $new = array_map('strval', $newAllowedRoles);

        $added   = array_values(array_diff($new, $old)); // نقش‌هایی که تازه Provider شده‌اند
        $removed = array_values(array_diff($old, $new)); // نقش‌هایی که دیگر Provider نیستند

        $allowCreation      = (bool) $settings->allow_role_service_creation;
        $protectedRoleNames = ['super-admin', 'admin'];

        // پرمیشن‌هایی که Provider همیشه باید داشته باشد
        $providerAlwaysPerms   = ['booking.services.view'];
        // پرمیشن‌هایی که فقط در صورت allow_role_service_creation = true داده می‌شوند
        $providerCreationPerms = ['booking.services.create', 'booking.services.edit', 'booking.services.delete'];

        // 1) نقش‌هایی که تازه Provider شده‌اند → همیشه view بگیرند
        if (!empty($new)) {
            $roles = Role::query()->whereIn('id', $new)->get();
            foreach ($roles as $role) {
                foreach ($providerAlwaysPerms as $perm) {
                    if (! $role->hasPermissionTo($perm)) {
                        $role->givePermissionTo($perm);
                    }
                }
            }
        }

        // 2) نقش‌هایی که دیگر Provider نیستند → تمام پرمیشن‌های booking.services.* ازشان گرفته شود
        if (!empty($removed)) {
            $roles = Role::query()->whereIn('id', $removed)->get();
            foreach ($roles as $role) {
                if (in_array($role->name, $protectedRoleNames, true)) {
                    // admin / super-admin را دست نزن
                    continue;
                }
                foreach (array_merge($providerAlwaysPerms, $providerCreationPerms) as $perm) {
                    if ($role->hasPermissionTo($perm)) {
                        $role->revokePermissionTo($perm);
                    }
                }
            }
        }

        // 3) مدیریت پرمیشن‌های create/edit/delete بر اساس allow_role_service_creation
        if (!empty($new)) {
            $roles = Role::query()->whereIn('id', $new)->get();
            foreach ($roles as $role) {
                if (in_array($role->name, $protectedRoleNames, true)) {
                    // admin / super-admin در Installer همه پرمیشن‌ها را گرفته‌اند، دست نمی‌زنیم
                    continue;
                }

                if ($allowCreation) {
                    // فعال: Providerها اجازه ساخت/ویرایش/حذف سرویس دارند
                    foreach ($providerCreationPerms as $perm) {
                        if (! $role->hasPermissionTo($perm)) {
                            $role->givePermissionTo($perm);
                        }
                    }
                } else {
                    // غیرفعال: فقط view بماند، ساخت/ویرایش/حذف را بگیر
                    foreach ($providerCreationPerms as $perm) {
                        if ($role->hasPermissionTo($perm)) {
                            $role->revokePermissionTo($perm);
                        }
                    }
                }
            }
        }

        // ---------------------------
        // به‌روزرسانی برنامه زمانی سراسری (همان کد قبلی خودت)
        // ---------------------------
        $rulesInput = $request->input('rules', []);
        for ($weekday = 0; $weekday <= 6; $weekday++) {
            $input = $rulesInput[$weekday] ?? null;
            if ($input === null) {
                continue;
            }

            $isClosed = (bool)($input['is_closed'] ?? false);
            $start    = $input['work_start_local'] ?? null;
            $end      = $input['work_end_local'] ?? null;

            $breaks = [];
            if (isset($input['breaks']) && is_array($input['breaks'])) {
                foreach ($input['breaks'] as $row) {
                    $bStart = trim($row['start_local'] ?? '');
                    $bEnd   = trim($row['end_local'] ?? '');
                    if ($bStart !== '' && $bEnd !== '') {
                        $breaks[] = ['start_local' => $bStart, 'end_local' => $bEnd];
                    }
                }
            } elseif (isset($input['breaks_json'])) {
                $bVal = $input['breaks_json'];
                if (is_string($bVal)) {
                    $decoded = json_decode($bVal, true);
                    $breaks = is_array($decoded) ? $decoded : [];
                } elseif (is_array($bVal)) {
                    $breaks = $bVal;
                }
            }

            $slotDuration = isset($input['slot_duration_minutes']) && $input['slot_duration_minutes'] !== '' ? (int)$input['slot_duration_minutes'] : null;
            $capSlot      = isset($input['capacity_per_slot']) && $input['capacity_per_slot'] !== '' ? (int)$input['capacity_per_slot'] : null;
            $capDay       = isset($input['capacity_per_day']) && $input['capacity_per_day'] !== '' ? (int)$input['capacity_per_day'] : null;

            BookingAvailabilityRule::updateOrCreate(
                [
                    'scope_type' => BookingAvailabilityRule::SCOPE_GLOBAL,
                    'scope_id'   => null,
                    'weekday'    => $weekday,
                ],
                [
                    'is_closed'            => $isClosed,
                    'work_start_local'     => $isClosed ? null : ($start ?: null),
                    'work_end_local'       => $isClosed ? null : ($end ?: null),
                    'breaks_json'          => $isClosed ? [] : $breaks,
                    'slot_duration_minutes'=> $isClosed ? null : $slotDuration,
                    'capacity_per_slot'    => $isClosed ? null : $capSlot,
                    'capacity_per_day'     => $isClosed ? null : $capDay,
                ]
            );
        }

        return redirect()->route('user.booking.settings.edit')->with('success', 'تنظیمات و برنامه زمانی ذخیره شد.');
    }

}
