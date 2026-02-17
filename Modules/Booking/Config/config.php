<?php

return [

    'timezones' => [
        // All datetimes stored in DB
        'storage' => 'UTC',

        // Schedule rules are stored as local times (e.g., Asia/Tehran) and converted to UTC on slot generation
        'schedule' => 'Asia/Tehran',

        // Default display timezone for UI
        'display_default' => 'Asia/Tehran',
    ],

    'labels' => [
        'provider' => 'ارائه‌دهنده',
        'providers' => 'ارائه‌دهندگان',
    ],

    'defaults' => [
        'currency_unit' => 'IRR',
        'global_online_booking_enabled' => true,
        'slot_duration_minutes' => 30,
        'capacity_per_slot' => 1,
        'capacity_per_day' => null,

        // Per weekday default windows (local time). Used only if no GLOBAL rule is configured.
        // weekday: 0=Sunday ... 6=Saturday (Carbon)
        'work_windows' => [
            0 => [['start' => '09:00', 'end' => '17:00']],
            1 => [['start' => '09:00', 'end' => '17:00']],
            2 => [['start' => '09:00', 'end' => '17:00']],
            3 => [['start' => '09:00', 'end' => '17:00']],
            4 => [['start' => '09:00', 'end' => '17:00']],
            5 => [], // Friday closed by default
            6 => [['start' => '09:00', 'end' => '17:00']],
        ],

        'breaks' => [
            0 => [['start_local' => '13:00', 'end_local' => '14:00']],
            1 => [['start_local' => '13:00', 'end_local' => '14:00']],
            2 => [['start_local' => '13:00', 'end_local' => '14:00']],
            3 => [['start_local' => '13:00', 'end_local' => '14:00']],
            4 => [['start_local' => '13:00', 'end_local' => '14:00']],
            5 => [],
            6 => [['start_local' => '13:00', 'end_local' => '14:00']],
        ],
    ],

    // Slot hold TTL for online booking (minutes)
    'slot_hold_ttl_minutes' => 20,

    // Payment timeout for pending appointments (minutes)
    'payment_timeout_minutes' => 20,

    // Which appointment statuses consume capacity (booked count)
    'capacity_consuming_statuses' => [
        \Modules\Booking\Entities\Appointment::STATUS_CONFIRMED,
        \Modules\Booking\Entities\Appointment::STATUS_PENDING_PAYMENT,
    ],

    'integrations' => [
        'reminders' => [
            'enabled' => true,
            'default_templates' => [
                ['target' => 'CLIENT', 'offset_minutes' => -1440, 'channel' => 'SMS'],   // -24h
                ['target' => 'CLIENT', 'offset_minutes' => -120, 'channel' => 'IN_APP'], // -2h (ignored unless client in-app exists)
                ['target' => 'PROVIDER', 'offset_minutes' => -30, 'channel' => 'IN_APP'], // -30m
            ],
        ],

        'tasks' => [
            'enabled' => true,
            'create_provider_task_on_confirm' => false,
            'create_followup_on_no_show' => true,
            'create_followup_on_cancel' => false,
        ],

        'workflows' => [
            'enabled' => true,
            // map internal keys to Workflows module workflow_keys
            // If null, the key itself is used.
            'workflow_keys' => [
                'appointment_created'        => null,
                'appointment_created_online' => null,
                'appointment_confirmed'      => null,
                'appointment_status_changed' => null,
                'appointment_canceled'       => null,
                'appointment_done'           => null,
                'appointment_rescheduled'    => null,
                'appointment_no_show'        => null,

                // Statement workflows
                'statement_created'          => null,
                'statement_created_draft'    => null,
                'statement_created_approved' => null,
                'statement_created_completed'=> null,
                'statement_status_changed'   => null,
                'statement_approved'         => null,
                'statement_completed'        => null,

                // Time-based triggers (mapped automatically in code, but listed here for reference)
                'appointment_reminder_1_hour_before'  => null,
                'appointment_reminder_2_hours_before' => null,
                'appointment_reminder_1_day_before'   => null,
                'appointment_reminder_2_days_before'  => null,
                'appointment_reminder_3_days_before'  => null,
                'appointment_reminder_7_days_before'  => null,
            ],

            // DEPRECATED: This array is no longer used.
            // Reminders are now dynamically created based on active workflows matching 'appointment_reminder_%'
            'reminder_offsets_minutes' => [],
        ],
    ],
];
