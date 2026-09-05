<?php

namespace Modules\Booking\Entities;

use Illuminate\Database\Eloquent\Model;

class BookingSetting extends Model
{
    protected $table = 'booking_settings';

    protected $fillable = [
        'currency_unit',
        'global_online_booking_enabled',
        'default_slot_duration_minutes',
        'default_capacity_per_slot',
        'default_capacity_per_day',
        'default_buffer_before_minutes',
        'default_buffer_after_minutes',
        'allow_role_service_creation',
        'allowed_roles',
        'statement_roles',
            'category_management_scope',
            'form_management_scope',
            'service_category_selection_scope',
            'service_form_selection_scope',
            'operator_appointment_flow',
            'user_appointment_flow',
            'allow_appointment_entry_exit_times',
            'allow_manual_time_override',
            'tax_enabled',
            'tax_type',
            'tax_amount',
            'cure_default_status',
            'cure_allow_edit_confirmed',
            'cure_allow_discount',
            'cure_max_discount_percent',
            'cure_discount_type',
            'cure_auto_tax',
            'cure_warranty_enabled',
            'cure_default_warranty_months',
            'cure_default_warranty_text',
            'cure_default_notes',
            'cure_require_notes',
            'cure_tooth_numbering_system',
            'cure_auto_highlight_teeth',
            'cure_show_tooth_filter',
            'cure_allowed_categories',
            'cure_statuses',
            'cure_assignable_roles',
            'show_service_description',
            'show_supplementary_info',
            'show_provider_info',
            'ads',
            'appointment_statuses',
            'queue_enabled',
            'queue_max_size',
            'monitoring_quick_status_enabled',
            'monitoring_refresh_interval_seconds',
            'key',
            'value',
        ];

        protected $casts = [
            'global_online_booking_enabled' => 'boolean',
            'queue_enabled' => 'boolean',
            'queue_max_size' => 'integer',
            'monitoring_quick_status_enabled' => 'boolean',
            'monitoring_refresh_interval_seconds' => 'integer',
            'allow_role_service_creation' => 'boolean',
            'allowed_roles' => 'array',
            'statement_roles' => 'array',
            'allow_appointment_entry_exit_times' => 'boolean',
            'allow_manual_time_override' => 'boolean',
            'tax_enabled' => 'boolean',
            'cure_allow_edit_confirmed' => 'boolean',
            'cure_allow_discount' => 'boolean',
            'cure_auto_tax' => 'boolean',
            'cure_warranty_enabled' => 'boolean',
            'cure_require_notes' => 'boolean',
            'cure_auto_highlight_teeth' => 'boolean',
            'cure_show_tooth_filter' => 'boolean',
            'cure_allowed_categories' => 'array',
            'cure_statuses' => 'array',
            'cure_assignable_roles' => 'array',
            'show_service_description' => 'boolean',
            'show_supplementary_info' => 'boolean',
            'show_provider_info' => 'boolean',
            'ads' => 'array',
            'appointment_statuses' => 'array',
        ];

        public function getAdsAttribute($value): array
        {
            $defaults = [
                'doctor_page' => [
                    'enabled'         => false,
                    'desktop_image'   => null,
                    'mobile_image'    => null,
                    'link'            => null,
                    'open_in_new_tab' => true,
                    'alt_text'        => null,
                ],
            ];

            if (empty($value)) {
                return $defaults;
            }

            $decoded = is_string($value) ? json_decode($value, true) : $value;
            if (!is_array($decoded)) {
                return $defaults;
            }

            return array_replace_recursive($defaults, $decoded);
        }

        public function isDoctorBannerEnabled(): bool
        {
            return (bool) ($this->ads['doctor_page']['enabled'] ?? false);
        }

        public function getDoctorBannerDesktopUrlAttribute(): ?string
        {
            $path = $this->ads['doctor_page']['desktop_image'] ?? null;
            return $path ? asset('storage/' . $path) : null;
        }

        public function getDoctorBannerMobileUrlAttribute(): ?string
        {
            $path = $this->ads['doctor_page']['mobile_image'] ?? null;
            return $path ? asset('storage/' . $path) : null;
        }

        public function getDoctorBannerLinkAttribute(): ?string
        {
            return $this->ads['doctor_page']['link'] ?? null;
        }

        public function getDoctorBannerOpenNewTabAttribute(): bool
        {
            return (bool) ($this->ads['doctor_page']['open_in_new_tab'] ?? true);
        }

        public function getDoctorBannerAltAttribute(): ?string
        {
            return $this->ads['doctor_page']['alt_text'] ?? null;
        }

        public static function defaultAppointmentStatuses(): array
        {
            return [
                [
                    'id' => 'CONFIRMED',
                    'name' => 'تایید شده',
                    'color' => '#10b981',
                    'order' => 1,
                    'step_booking_enabled' => true,
                    'schedule_booking_enabled' => true,
                ],
                [
                    'id' => 'PENDING',
                    'name' => 'در انتظار تایید',
                    'color' => '#f59e0b',
                    'order' => 2,
                    'step_booking_enabled' => true,
                    'schedule_booking_enabled' => true,
                ],
                [
                    'id' => 'PENDING_PAYMENT',
                    'name' => 'در انتظار پرداخت',
                    'color' => '#f97316',
                    'order' => 3,
                    'step_booking_enabled' => true,
                    'schedule_booking_enabled' => true,
                ],
                [
                    'id' => 'DONE',
                    'name' => 'انجام شده',
                    'color' => '#3b82f6',
                    'order' => 4,
                    'step_booking_enabled' => true,
                    'schedule_booking_enabled' => true,
                ],
                [
                    'id' => 'CANCELED_BY_CLIENT',
                    'name' => 'لغو توسط مشتری',
                    'color' => '#ef4444',
                    'order' => 5,
                    'step_booking_enabled' => true,
                    'schedule_booking_enabled' => true,
                ],
                [
                    'id' => 'CANCELED_BY_ADMIN',
                    'name' => 'لغو توسط ادمین',
                    'color' => '#dc2626',
                    'order' => 6,
                    'step_booking_enabled' => true,
                    'schedule_booking_enabled' => true,
                ],
                [
                    'id' => 'NO_SHOW',
                    'name' => 'عدم حضور',
                    'color' => '#64748b',
                    'order' => 7,
                    'step_booking_enabled' => true,
                    'schedule_booking_enabled' => true,
                ],
                [
                    'id' => 'RESCHEDULED',
                    'name' => 'جابجا شده',
                    'color' => '#8b5cf6',
                    'order' => 8,
                    'step_booking_enabled' => true,
                    'schedule_booking_enabled' => true,
                ],
                [
                    'id' => 'DRAFT',
                    'name' => 'پیش‌نویس',
                    'color' => '#6b7280',
                    'order' => 9,
                    'step_booking_enabled' => true,
                    'schedule_booking_enabled' => true,
                ],
            ];
        }

        public function getAppointmentStatusesAttribute($value): array
        {
            $defaults = static::defaultAppointmentStatuses();
            if (empty($value)) {
                return $defaults;
            }

            $saved = is_string($value) ? json_decode($value, true) : $value;
            if (!is_array($saved) || empty($saved)) {
                return $defaults;
            }

            $savedMap = [];
            foreach ($saved as $item) {
                if (!empty($item['id'])) {
                    $savedMap[$item['id']] = $item;
                }
            }

            $result = [];
            foreach ($defaults as $def) {
                $id = $def['id'];
                if (isset($savedMap[$id])) {
                    $merged = array_merge($def, $savedMap[$id]);
                    $merged['step_booking_enabled'] = (bool)($merged['step_booking_enabled'] ?? true);
                    $merged['schedule_booking_enabled'] = (bool)($merged['schedule_booking_enabled'] ?? true);
                    $result[] = $merged;
                    unset($savedMap[$id]);
                } else {
                    $result[] = $def;
                }
            }

            foreach ($savedMap as $extra) {
                $extra['step_booking_enabled'] = (bool)($extra['step_booking_enabled'] ?? true);
                $extra['schedule_booking_enabled'] = (bool)($extra['schedule_booking_enabled'] ?? true);
                $result[] = $extra;
            }

            usort($result, fn($a, $b) => ($a['order'] ?? 99) <=> ($b['order'] ?? 99));

            return $result;
        }

        public static function current(): self
        {
            $row = static::query()->whereNull('key')->first();
            if ($row) return $row;

            $defaults = (array) config('booking.defaults', []);

            return static::query()->create([
                'currency_unit' => $defaults['currency_unit'] ?? 'IRR',
                'global_online_booking_enabled' => $defaults['global_online_booking_enabled'] ?? true,
                'default_slot_duration_minutes' => $defaults['slot_duration_minutes'] ?? 30,
                'default_capacity_per_slot' => $defaults['capacity_per_slot'] ?? 1,
                'default_capacity_per_day' => $defaults['capacity_per_day'] ?? null,
                'allow_role_service_creation' => false,
                'allowed_roles' => [],
                'statement_roles' => [],
                'category_management_scope' => 'ALL',
                'form_management_scope' => 'ALL',
                'service_category_selection_scope' => 'ALL',
                'service_form_selection_scope' => 'ALL',
                'operator_appointment_flow' => 'PROVIDER_FIRST',
                'user_appointment_flow' => 'SERVICE_FIRST',
                'allow_appointment_entry_exit_times' => false,
                'allow_manual_time_override' => false,
                'tax_enabled' => false,
                'tax_type' => 'PERCENT',
                'tax_amount' => null,

                'cure_default_status' => 'draft',
            'cure_allow_edit_confirmed' => false,
            'cure_allow_discount' => true,
            'cure_max_discount_percent' => 100,
            'cure_discount_type' => 'amount',
            'cure_auto_tax' => false,
            'cure_warranty_enabled' => false,
            'cure_default_warranty_months' => 6,
            'cure_default_warranty_text' => null,
            'cure_default_notes' => null,
            'cure_require_notes' => false,
            'cure_tooth_numbering_system' => 'universal',
            'cure_auto_highlight_teeth' => true,
            'cure_show_tooth_filter' => true,
            'cure_allowed_categories' => [],
            'cure_statuses' => [
                [ 'id' => 'draft',     'name' => 'پیش‌نویس',       'color' => '#6b7280', 'order' => 1, 'allowed_roles' => [], 'allowed_from' => [] ],
                [ 'id' => 'pricing',   'name' => 'محاسبه هزینه',   'color' => '#f59e0b', 'order' => 2, 'allowed_roles' => [], 'allowed_from' => ['draft'] ],
                [ 'id' => 'approved1', 'name' => 'تایید اولیه',     'color' => '#3b82f6', 'order' => 3, 'allowed_roles' => [], 'allowed_from' => ['draft','pricing'] ],
                [ 'id' => 'approved2', 'name' => 'تایید نهایی',     'color' => '#10b981', 'order' => 4, 'allowed_roles' => [], 'allowed_from' => ['approved1'] ]
            ],
            'cure_assignable_roles' => [],
            'show_service_description' => true,
            'show_supplementary_info' => true,
            'show_provider_info' => true,
            'monitoring_quick_status_enabled' => false,
            'monitoring_refresh_interval_seconds' => 15,
            'appointment_statuses' => static::defaultAppointmentStatuses(),
        ]);
    }

    public function isMonitoringQuickStatusEnabled(): bool
    {
        return (bool) ($this->monitoring_quick_status_enabled ?? false);
    }

    public function getMonitoringRefreshInterval(): int
    {
        $interval = (int) ($this->monitoring_refresh_interval_seconds ?? 15);
        return max(5, $interval);
    }

    public static function isQueueEnabled(): bool
    {
        $setting = static::current();
        return (bool) ($setting->queue_enabled ?? false);
    }

    public static function getValue(string $key, $default = null)
    {
        $row = static::query()->where('key', $key)->first();
        return $row ? $row->value : $default;
    }

    public static function setValue(string $key, $value)
    {
        return static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
