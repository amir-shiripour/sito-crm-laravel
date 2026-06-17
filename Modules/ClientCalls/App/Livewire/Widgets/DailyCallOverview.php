<?php

namespace Modules\ClientCalls\App\Livewire\Widgets;

use Livewire\Component;
use Modules\ClientCalls\Entities\ClientCall;
use Illuminate\Support\Facades\DB;

class DailyCallOverview extends Component
{
    public function render()
    {
        $user = auth()->user();
        $today = now()->toDateString();

        // پایه کوئری با رعایت دسترسی دیدن تماس‌های کاربر جاری
        $baseQuery = ClientCall::query()->visibleForUser($user)->whereDate('call_date', $today);

        // ۱. تعداد کل بیماران یکتا که امروز با آن‌ها تماس گرفته شده
        $totalUniqueClientsContacted = (clone $baseQuery)->distinct('client_id')->count('client_id');

        // ۲. تعداد بیماران یکتا با حداقل یک تماس موفق (done) در امروز
        $successfulClientsCount = (clone $baseQuery)
            ->where('status', 'done')
            ->distinct('client_id')
            ->count('client_id');

        // ۳. تعداد بیماران یکتا که تماس امروز با آن‌ها ناموفق بوده و هیچ تماس موفقی هم نداشته‌اند
        $failedClientsCount = (clone $baseQuery)
            ->where('status', 'failed')
            ->whereNotIn('client_id', function ($query) use ($today) {
                $query->select('client_id')
                    ->from('client_calls')
                    ->whereDate('call_date', $today)
                    ->where('status', 'done');
            })
            ->distinct('client_id')
            ->count('client_id');

        // ۴. آمار کل قیف وضعیت تماس‌های امروز برای چارت کوچک داخلی
        $statusCounts = (clone $baseQuery)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('clientcalls::livewire.widgets.daily-overview', [
            'totalClients' => $totalUniqueClientsContacted,
            'successfulClients' => $successfulClientsCount,
            'failedClients' => $failedClientsCount,
            'statusFunnel' => [
                'planned'   => $statusCounts['planned'] ?? 0,
                'done'      => $statusCounts['done'] ?? 0,
                'failed'    => $statusCounts['failed'] ?? 0,
                'cancelled' => $statusCounts['cancelled'] ?? 0,
            ]
        ]);
    }

    public function refreshWidget() {
        // این متد فقط باعث Re-render شدن کامپوننت لایووایر می‌شود
    }
}
