<?php

namespace App\Contracts;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

interface CalendarEventSource
{
    /**
     * دریافت رویدادهای یک روز خاص برای کاربر
     */
    public function getEventsForDate(Carbon $date, User $user): Collection;

    /**
     * دریافت رویدادهای بازه زمانی برای کاربر
     */
    public function getEventsForRange(Carbon $from, Carbon $to, User $user): Collection;

    /**
     * کلید یکتای منبع (مثلاً: 'booking', 'tasks', 'reminders')
     */
    public function getSourceKey(): string;

    /**
     * عنوان فارسی منبع (مثلاً: 'نوبت‌دهی', 'وظایف', 'یادآوری‌ها')
     */
    public function getSourceLabel(): string;
}
