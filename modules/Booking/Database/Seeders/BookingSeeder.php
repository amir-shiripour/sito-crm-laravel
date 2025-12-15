<?php

namespace Modules\Booking\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Booking\Entities\BookingSetting;
use Modules\Booking\Entities\BookingAvailabilityRule;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure a settings row exists
        $settings = BookingSetting::current();

        // Create a simple default GLOBAL schedule if none exists:
        // Sunday-Thursday & Saturday: 09:00-17:00, Friday closed
        for ($weekday = 0; $weekday <= 6; $weekday++) {
            $exists = BookingAvailabilityRule::query()
                ->where('scope_type', BookingAvailabilityRule::SCOPE_GLOBAL)
                ->where('weekday', $weekday)
                ->exists();

            if ($exists) continue;

            $isFriday = ($weekday === 5);

            BookingAvailabilityRule::query()->create([
                'scope_type' => BookingAvailabilityRule::SCOPE_GLOBAL,
                'scope_id' => null,
                'weekday' => $weekday,
                'is_closed' => $isFriday,
                'work_start_local' => $isFriday ? null : '09:00',
                'work_end_local' => $isFriday ? null : '17:00',
                'breaks_json' => [],
                'slot_duration_minutes' => $settings->default_slot_duration_minutes,
                'capacity_per_slot' => $settings->default_capacity_per_slot,
                'capacity_per_day' => $settings->default_capacity_per_day,
            ]);
        }
    }
}
