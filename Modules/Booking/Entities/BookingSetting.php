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
        'key',
        'value',
    ];

    protected $casts = [
        'global_online_booking_enabled' => 'boolean',
        'allow_role_service_creation' => 'boolean',
        'allowed_roles' => 'array',
        'statement_roles' => 'array',
        'allow_appointment_entry_exit_times' => 'boolean',
        'tax_enabled' => 'boolean',
        'cure_allow_edit_confirmed' => 'boolean',
        'cure_allow_discount' => 'boolean',
        'cure_auto_tax' => 'boolean',
        'cure_warranty_enabled' => 'boolean',
        'cure_require_notes' => 'boolean',
        'cure_auto_highlight_teeth' => 'boolean',
        'cure_show_tooth_filter' => 'boolean',
    ];

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
        ]);
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
