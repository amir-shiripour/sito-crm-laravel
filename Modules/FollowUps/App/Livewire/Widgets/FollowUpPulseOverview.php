<?php

namespace Modules\FollowUps\App\Livewire\Widgets;

use Livewire\Component;
use Modules\FollowUps\Entities\FollowUp;
use Modules\Tasks\Entities\Task;

class FollowUpPulseOverview extends Component
{
    public function render()
    {
        $user = auth()->user();
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        // دریافت کلاینت‌های قابل مشاهده برای اعمال فیلتر سطح دسترسی روی پیگیری‌ها
        $visibleClientIds = \Modules\Clients\Entities\Client::visibleForUser($user)->pluck('id')->toArray();

        $baseQuery = FollowUp::query()
            ->where('related_type', Task::RELATED_TYPE_CLIENT)
            ->whereIn('related_id', $visibleClientIds);

        // ۱. پیگیری‌های معوقه (تاریخ گذشته و تکمیل نشده)
        $overdueCount = (clone $baseQuery)
            ->where('due_at', '<', $todayStart)
            ->where('status', '!=', 'completed') // بسته به استرینگ وضعیت در پروژه شما (مانند pending یا open)
            ->count();

        // ۲. کل پیگیری‌های امروز
        $todayQuery = (clone $baseQuery)->whereBetween('due_at', [$todayStart, $todayEnd]);

        $todayTotal = (clone $todayQuery)->count();
        $todayCompleted = (clone $todayQuery)->where('status', 'completed')->count();
        $todayPending = $todayTotal - $todayCompleted;

        // ۳. بار کاری ۷ روز آینده برای چارت هفتگی
        $upcomingLoad = [];
        for ($i = 1; $i <= 7; $i++) {
            $day = now()->addDays($i);
            $dayCount = (clone $baseQuery)
                ->whereBetween('due_at', [$day->copy()->startOfDay(), $day->copy()->endOfDay()])
                ->count();

            $upcomingLoad[\Morilog\Jalali\Jalalian::fromCarbon($day)->format('l')] = $dayCount;
        }

        return view('followups::livewire.widgets.pulse-overview', [
            'overdueCount'   => $overdueCount,
            'todayTotal'     => $todayTotal,
            'todayCompleted' => $todayCompleted,
            'todayPending'   => $todayPending,
            'upcomingLoad'   => $upcomingLoad
        ]);
    }
}
