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

        $rolesQuery = Role::orderBy('name');
        if (!auth()->user() || !auth()->user()->hasRole('super-admin')) {
            $rolesQuery->where('name', '!=', 'super-admin');
        }
        $roles = $rolesQuery->get();
        $categories = \Modules\Booking\Entities\BookingCategory::orderBy('name')->get();

        return view('booking::user.settings.edit', compact('settings', 'rules', 'roles', 'categories'));
    }

    public function update(Request $request)
    {
        $settings = BookingSetting::current();
        $shouldLog = (bool) config('app.debug') || (bool) config('booking.debug_logs', false);

        // ── Helper: normalize roles to IDs ──
        $normalizeRolesToIds = function ($rolesInput): array {
            if (is_string($rolesInput)) {
                $decoded = json_decode($rolesInput, true);
                $rolesInput = is_array($decoded) ? $decoded : preg_split('/\s*,\s*/', trim($rolesInput), -1, PREG_SPLIT_NO_EMPTY);
            }
            if (!is_array($rolesInput)) $rolesInput = [];
            $rolesInput = array_values(array_filter($rolesInput, fn($v) => $v !== null && $v !== ''));

            $ids = [];
            foreach ($rolesInput as $v) {
                if (is_int($v) || (is_string($v) && ctype_digit($v))) {
                    if ((int)$v > 0) $ids[] = (int)$v;
                    continue;
                }
                if (is_string($v) && trim($v) !== '') {
                    $id = \Spatie\Permission\Models\Role::query()->where('name', trim($v))->value('id');
                    if ($id) $ids[] = (int)$id;
                }
            }
            return array_values(array_unique($ids));
        };

        $isSuperAdmin = auth()->user() && auth()->user()->hasRole('super-admin');
        $superAdminRole = \Spatie\Permission\Models\Role::where('name', 'super-admin')->first();
        $superAdminRoleId = $superAdminRole?->id;

        $syncRoleInput = function ($inputRoles, $oldRoles) use ($isSuperAdmin, $superAdminRoleId) {
            if ($isSuperAdmin || !$superAdminRoleId) {
                return $inputRoles;
            }
            $containsOldSuper = in_array($superAdminRoleId, $oldRoles);
            
            // Filter out super-admin from input
            $filteredInput = array_diff($inputRoles, [$superAdminRoleId]);
            
            if ($containsOldSuper) {
                $filteredInput[] = $superAdminRoleId;
            }
            return array_values($filteredInput);
        };

        $oldAllowedRoles = $normalizeRolesToIds($settings->allowed_roles ?? []);

        // ═══════════════════════════════════════
        //  PART 1: Validate & Save General Settings
        // ═══════════════════════════════════════
        $generalData = $request->validate([
            'currency_unit' => ['required', 'in:IRR,IRT'],
            'global_online_booking_enabled' => ['required'],
            'default_slot_duration_minutes' => ['required', 'integer', 'min:5', 'max:720'],
            'default_capacity_per_slot' => ['required', 'integer', 'min:1', 'max:1000'],
            'default_capacity_per_day' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'allow_role_service_creation' => ['required'],
            'allowed_roles' => ['nullable'],
            'statement_roles' => ['nullable'],
            'category_management_scope' => ['required', 'in:ALL,OWN'],
            'form_management_scope' => ['required', 'in:ALL,OWN'],
            'service_category_selection_scope' => ['required', 'in:ALL,OWN'],
            'service_form_selection_scope' => ['required', 'in:ALL,OWN'],
            'operator_appointment_flow' => ['required', 'in:PROVIDER_FIRST,SERVICE_FIRST'],
            'user_appointment_flow' => ['required', 'in:PROVIDER_FIRST,SERVICE_FIRST'],
            'allow_appointment_entry_exit_times' => ['required'],
            'tax_enabled' => ['required'],
            'tax_type' => ['nullable', 'in:PERCENT,FIXED'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $generalData['global_online_booking_enabled'] = (bool) $generalData['global_online_booking_enabled'];
        $generalData['allow_role_service_creation'] = (bool) $generalData['allow_role_service_creation'];
        $generalData['allow_appointment_entry_exit_times'] = (bool) $generalData['allow_appointment_entry_exit_times'];
        $generalData['tax_enabled'] = $request->boolean('tax_enabled');

        $settings->fill($generalData);
        $settings->allowed_roles = $syncRoleInput($normalizeRolesToIds($request->input('allowed_roles', [])), $oldAllowedRoles);
        $settings->statement_roles = $syncRoleInput($normalizeRolesToIds($request->input('statement_roles', [])), $normalizeRolesToIds($settings->statement_roles ?? []));

        // ═══════════════════════════════════════
        //  PART 2: Save Cure Settings DIRECTLY
        // ═══════════════════════════════════════
        // Bypass fill() — assign each cure field manually

        $settings->cure_default_status = $request->input('cure_default_status', 'draft');
        $settings->cure_allow_edit_confirmed = $request->boolean('cure_allow_edit_confirmed');
        $settings->cure_allow_discount = $request->boolean('cure_allow_discount');
        $settings->cure_max_discount_percent = (int) $request->input('cure_max_discount_percent', 100);
        $settings->cure_discount_type = $request->input('cure_discount_type', 'amount');
        $settings->cure_auto_tax = $request->boolean('cure_auto_tax');
        $settings->cure_warranty_enabled = $request->boolean('cure_warranty_enabled');
        $settings->cure_default_warranty_months = (int) $request->input('cure_default_warranty_months', 6);
        $settings->cure_default_warranty_text = $request->input('cure_default_warranty_text') ?: null;
        $settings->cure_default_notes = $request->input('cure_default_notes') ?: null;
        $settings->cure_require_notes = $request->boolean('cure_require_notes');
        $settings->cure_tooth_numbering_system = $request->input('cure_tooth_numbering_system', 'universal');
        $settings->cure_auto_highlight_teeth = $request->boolean('cure_auto_highlight_teeth');
        $settings->cure_show_tooth_filter = $request->boolean('cure_show_tooth_filter');

        $cureAllowedCategories = $request->input('cure_allowed_categories', []);
        if (is_array($cureAllowedCategories)) {
            $settings->cure_allowed_categories = array_values(array_map('intval', array_filter($cureAllowedCategories)));
        } else {
            $settings->cure_allowed_categories = [];
        }

        $cureStatusesInput = $request->input('cure_statuses', []);
        if (is_array($cureStatusesInput)) {
            usort($cureStatusesInput, fn($a, $b) => ($a['order'] ?? 99) <=> ($b['order'] ?? 99));
            $formattedStatuses = [];
            
            $oldStatuses = $settings->cure_statuses ?? [];
            $oldStatusRolesMap = [];
            foreach ($oldStatuses as $os) {
                if (!empty($os['id'])) {
                    $oldStatusRolesMap[$os['id']] = $os['allowed_roles'] ?? [];
                }
            }

            foreach ($cureStatusesInput as $st) {
                if (empty($st['id']) || empty($st['name'])) continue;
                $allowedRoles = [];
                if (isset($st['allowed_roles'])) {
                    $allowedRoles = $normalizeRolesToIds($st['allowed_roles']);
                }
                
                $oldRoles = $oldStatusRolesMap[trim($st['id'])] ?? [];
                $allowedRoles = $syncRoleInput($allowedRoles, $oldRoles);

                $allowedFrom = [];
                if (isset($st['allowed_from'])) {
                    $allowedFrom = is_array($st['allowed_from']) ? $st['allowed_from'] : array_filter(explode(',', $st['allowed_from']));
                }
                $formattedStatuses[] = [
                    'id' => trim($st['id']),
                    'name' => trim($st['name']),
                    'color' => trim($st['color'] ?? '#6b7280'),
                    'order' => (int) ($st['order'] ?? 1),
                    'allowed_roles' => $allowedRoles,
                    'allowed_from' => array_values(array_unique(array_filter($allowedFrom))),
                ];
            }
            $settings->cure_statuses = $formattedStatuses;
        } else {
            $settings->cure_statuses = [];
        }

        $settings->cure_assignable_roles = $syncRoleInput($normalizeRolesToIds($request->input('cure_assignable_roles', [])), $normalizeRolesToIds($settings->cure_assignable_roles ?? []));

        if ($shouldLog) {
            \Log::info('[Booking][Settings] Saving cure fields', [
                'cure_default_status' => $settings->cure_default_status,
                'cure_allow_discount' => $settings->cure_allow_discount,
                'cure_warranty_enabled' => $settings->cure_warranty_enabled,
                'cure_auto_tax' => $settings->cure_auto_tax,
            ]);
        }

        $settings->save();

        if ($shouldLog) {
            $fresh = $settings->fresh();
            \Log::info('[Booking][Settings] After save verification', [
                'cure_default_status' => $fresh->cure_default_status,
                'cure_allow_discount' => $fresh->cure_allow_discount,
            ]);
        }

        // ═══════════════════════════════════════
        //  PART 3: Manage Provider Role Permissions
        // ═══════════════════════════════════════
        $newAllowedRoles = (array) ($settings->allowed_roles ?? []);
        $old = array_map('strval', $oldAllowedRoles);
        $new = array_map('strval', $newAllowedRoles);
        $added = array_values(array_diff($new, $old));
        $removed = array_values(array_diff($old, $new));
        $allowCreation = (bool) $settings->allow_role_service_creation;
        $protectedRoleNames = ['super-admin', 'admin'];

        $providerAlwaysPerms = [
            'booking.view', 'booking.services.view', 'booking.categories.view',
            'booking.forms.view', 'booking.appointments.view',
            'booking.appointments.create', 'booking.appointments.edit',
        ];
        $providerCreationPerms = [
            'booking.services.create', 'booking.services.edit', 'booking.services.delete',
            'booking.categories.create', 'booking.categories.edit', 'booking.categories.delete',
            'booking.forms.create', 'booking.forms.edit', 'booking.forms.delete',
        ];

        if (!empty($new)) {
            foreach (\Spatie\Permission\Models\Role::query()->whereIn('id', $new)->get() as $role) {
                foreach ($providerAlwaysPerms as $perm) {
                    if (!$role->hasPermissionTo($perm)) $role->givePermissionTo($perm);
                }
                if (!in_array($role->name, $protectedRoleNames, true)) {
                    if ($allowCreation) {
                        foreach ($providerCreationPerms as $perm) {
                            if (!$role->hasPermissionTo($perm)) $role->givePermissionTo($perm);
                        }
                    } else {
                        foreach ($providerCreationPerms as $perm) {
                            if ($role->hasPermissionTo($perm)) $role->revokePermissionTo($perm);
                        }
                    }
                }
            }
        }

        if (!empty($removed)) {
            foreach (\Spatie\Permission\Models\Role::query()->whereIn('id', $removed)->get() as $role) {
                if (in_array($role->name, $protectedRoleNames, true)) continue;
                foreach (array_merge($providerAlwaysPerms, $providerCreationPerms) as $perm) {
                    if ($role->hasPermissionTo($perm)) $role->revokePermissionTo($perm);
                }
            }
        }

        // ═══════════════════════════════════════
        //  PART 4: Update Weekly Schedule
        // ═══════════════════════════════════════
        $rulesInput = $request->input('rules', []);
        for ($weekday = 0; $weekday <= 6; $weekday++) {
            $input = $rulesInput[$weekday] ?? null;
            if ($input === null) continue;

            $isClosed = (bool)($input['is_closed'] ?? false);
            $start = $input['work_start_local'] ?? null;
            $end = $input['work_end_local'] ?? null;

            $breaks = [];
            if (isset($input['breaks']) && is_array($input['breaks'])) {
                foreach ($input['breaks'] as $row) {
                    $bStart = trim($row['start_local'] ?? '');
                    $bEnd = trim($row['end_local'] ?? '');
                    if ($bStart !== '' && $bEnd !== '') {
                        $breaks[] = ['start_local' => $bStart, 'end_local' => $bEnd];
                    }
                }
            } elseif (isset($input['breaks_json'])) {
                $bVal = $input['breaks_json'];
                $breaks = is_string($bVal) ? (json_decode($bVal, true) ?: []) : (is_array($bVal) ? $bVal : []);
            }

            $slotDuration = isset($input['slot_duration_minutes']) && $input['slot_duration_minutes'] !== '' ? (int)$input['slot_duration_minutes'] : null;
            $capSlot = isset($input['capacity_per_slot']) && $input['capacity_per_slot'] !== '' ? (int)$input['capacity_per_slot'] : null;
            $capDay = isset($input['capacity_per_day']) && $input['capacity_per_day'] !== '' ? (int)$input['capacity_per_day'] : null;

            BookingAvailabilityRule::updateOrCreate(
                ['scope_type' => BookingAvailabilityRule::SCOPE_GLOBAL, 'scope_id' => null, 'weekday' => $weekday],
                [
                    'is_closed' => $isClosed,
                    'work_start_local' => $isClosed ? null : ($start ?: null),
                    'work_end_local' => $isClosed ? null : ($end ?: null),
                    'breaks_json' => $isClosed ? [] : $breaks,
                    'slot_duration_minutes' => $isClosed ? null : $slotDuration,
                    'capacity_per_slot' => $isClosed ? null : $capSlot,
                    'capacity_per_day' => $isClosed ? null : $capDay,
                ]
            );
        }

        // ═══════════════════════════════════════
        //  PART 5: Redirect back to the same tab
        // ═══════════════════════════════════════
        $activeTab = $request->input('_active_tab', 'general');
        return redirect()->route('user.booking.settings.edit', ['tab' => $activeTab])
            ->with('success', 'تنظیمات و برنامه زمانی ذخیره شد.');
    }}
