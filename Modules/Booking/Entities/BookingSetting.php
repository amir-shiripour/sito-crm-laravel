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
        'allow_appointment_entry_exit_times',
    ];

    protected $casts = [
        'global_online_booking_enabled' => 'boolean',
        'allow_role_service_creation' => 'boolean',
        'allowed_roles' => 'array',
        'statement_roles' => 'array',
        'allow_appointment_entry_exit_times' => 'boolean',
    ];

    public static function current(): self
    {
        $row = static::query()->first();
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
            'allow_appointment_entry_exit_times' => false,
        ]);
    }
}
