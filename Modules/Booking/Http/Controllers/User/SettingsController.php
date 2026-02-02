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

        $shouldLog = (bool) config('app.debug') || (bool) config('booking.debug_logs', false);

        // ---------------------------
        // Helper: نرمال‌سازی allowed_roles به ID نقش‌ها
        // ---------------------------
        $normalizeRolesToIds = function ($rolesInput) use ($shouldLog): array {
            if ($shouldLog) {
                Log::info('[Booking][Settings] roles raw input', [
                    'type' => gettype($rolesInput),
                    'raw'  => $rolesInput,
                ]);
            }

            // ممکن است از UI به صورت JSON string ارسال شود
            if (is_string($rolesInput)) {
                $decoded = json_decode($rolesInput, true);

                if ($shouldLog) {
                    Log::info('[Booking][Settings] roles decode attempt', [
                        'is_json' => is_array($decoded),
                        'decoded' => $decoded,
                    ]);
                }

                // اگر JSON نبود، مثل CSV هم پشتیبانی کن
                if (is_array($decoded)) {
                    $rolesInput = $decoded;
                } else {
                    $rolesInput = preg_split('/\s*,\s*/', trim($rolesInput), -1, PREG_SPLIT_NO_EMPTY) ?: [];
                }
            }

            if (!is_array($rolesInput)) {
                $rolesInput = [];
            }

            $rolesInput = array_values(array_filter($rolesInput, fn ($v) => $v !== null && $v !== ''));

            if ($shouldLog) {
                Log::info('[Booking][Settings] roles filtered', [
                    'filtered' => $rolesInput,
                ]);
            }

            $normalizedRoleIds = [];
            foreach ($rolesInput as $v) {
                // اگر ID عددی بود
                if (is_int($v) || (is_string($v) && ctype_digit($v))) {
                    $id = (int) $v;
                    if ($id > 0) {
                        $normalizedRoleIds[] = $id;
                    }
                    continue;
                }

                // اگر name نقش ارسال شده بود، به ID تبدیل کن
                if (is_string($v)) {
                    $name = trim($v);
                    if ($name !== '') {
                        $id = Role::query()->where('name', $name)->value('id');
                        if ($id) {
                            $normalizedRoleIds[] = (int) $id;
                        } else {
                            if ($shouldLog) {
                                Log::warning('[Booking][Settings] unknown role name in roles', [
                                    'name' => $name,
                                ]);
                            }
                        }
                    }
                }
            }

            $final = array_values(array_unique($normalizedRoleIds));

            if ($shouldLog) {
                Log::info('[Booking][Settings] roles normalized', [
                    'normalized_ids' => $final,
                ]);
            }

            return $final;
        };

        // قدیمی‌ها قبل از آپدیت برای محاسبه تفاوت (نرمال‌شده به ID)
        $oldAllowedRoles = $normalizeRolesToIds($settings->allowed_roles ?? []);

        $data = $request->validate([
            'currency_unit' => ['required', Rule::in(['IRR', 'IRT'])],
            'global_online_booking_enabled' => ['required'],
            'default_slot_duration_minutes' => ['required', 'integer', 'min:5', 'max:720'],
            'default_capacity_per_slot' => ['required', 'integer', 'min:1', 'max:1000'],
            'default_capacity_per_day' => ['nullable', 'integer', 'min:1', 'max:10000'],

            'allow_role_service_creation' => ['required'],
            'allowed_roles' => ['nullable'],
            'statement_roles' => ['nullable'],

            'category_management_scope' => ['required', Rule::in(['ALL', 'OWN'])],
            'form_management_scope' => ['required', Rule::in(['ALL', 'OWN'])],
            'service_category_selection_scope' => ['required', Rule::in(['ALL', 'OWN'])],
            'service_form_selection_scope' => ['required', Rule::in(['ALL', 'OWN'])],
            'operator_appointment_flow' => ['required', Rule::in(['PROVIDER_FIRST', 'SERVICE_FIRST'])],
            'allow_appointment_entry_exit_times' => ['required'],
        ]);

        $data['global_online_booking_enabled'] = (bool) $data['global_online_booking_enabled'];
        $data['allow_role_service_creation']   = (bool) $data['allow_role_service_creation'];
        $data['allow_appointment_entry_exit_times'] = (bool) $data['allow_appointment_entry_exit_times'];

        // پر کردن فیلدهای ساده
        $settings->fill($data);

        // نرمال‌سازی allowed_roles (همیشه به ID نقش‌ها)
        $rolesInput = $request->input('allowed_roles', []);
        $settings->allowed_roles = $normalizeRolesToIds($rolesInput);

        // نرمال‌سازی statement_roles
        $statementRolesInput = $request->input('statement_roles', []);
        $settings->statement_roles = $normalizeRolesToIds($statementRolesInput);

        if ($shouldLog) {
            Log::info('[Booking][Settings] BEFORE save snapshot', [
                'booking_setting_id' => $settings->id ?? null,
                'old_allowed_roles'  => $oldAllowedRoles,
                'to_save_allowed_roles' => $settings->allowed_roles,
                'to_save_statement_roles' => $settings->statement_roles,
                'allow_role_service_creation' => (bool) $settings->allow_role_service_creation,
            ]);
        }

        $settings->save();

        if ($shouldLog) {
            Log::info('[Booking][Settings] AFTER save snapshot', [
                'saved_allowed_roles' => $settings->fresh()->allowed_roles,
            ]);
        }

        // ---------------------------
        // مدیریت Permission نقش‌های Provider
        // ---------------------------

        // جدیدها بعد از ذخیره (IDها)
        $newAllowedRoles = (array) ($settings->allowed_roles ?? []);

        // برای diff مطمئن باش همه چیز ID و به شکل string یکسان شده
        $old = array_map('strval', $oldAllowedRoles);
        $new = array_map('strval', $newAllowedRoles);

        $added   = array_values(array_diff($new, $old)); // نقش‌هایی که تازه Provider شده‌اند
        $removed = array_values(array_diff($old, $new)); // نقش‌هایی که دیگر Provider نیستند

        $allowCreation      = (bool) $settings->allow_role_service_creation;
        $protectedRoleNames = ['super-admin', 'admin'];

        if ($shouldLog) {
            Log::info('[Booking][Settings] allowed_roles diff', [
                'old' => $oldAllowedRoles,
                'new' => $newAllowedRoles,
                'added' => $added,
                'removed' => $removed,
                'allow_creation' => $allowCreation,
            ]);
        }

        // پرمیشن‌هایی که Provider همیشه باید داشته باشد
        $providerAlwaysPerms   = [
            'booking.view',
            'booking.services.view',
            'booking.categories.view',
            'booking.forms.view',
            'booking.appointments.view',
            'booking.appointments.create',
            'booking.appointments.edit',
        ];
        // پرمیشن‌هایی که فقط در صورت allow_role_service_creation = true داده می‌شوند
        $providerCreationPerms = [
            'booking.services.create',
            'booking.services.edit',
            'booking.services.delete',
            'booking.categories.create',
            'booking.categories.edit',
            'booking.categories.delete',
            'booking.forms.create',
            'booking.forms.edit',
            'booking.forms.delete',
        ];

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
