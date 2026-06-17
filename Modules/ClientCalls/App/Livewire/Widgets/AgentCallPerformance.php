<?php

namespace Modules\ClientCalls\App\Livewire\Widgets;

use Livewire\Component;
use Modules\ClientCalls\Entities\ClientCall;
use Illuminate\Support\Facades\DB;

class AgentCallPerformance extends Component
{
    public function render()
    {
        $user = auth()->user();
        $today = now()->toDateString();

        // استخراج عملکرد کاربران با شمارش تفکیکی تماس‌های موفق و ناموفق
        $agentsPerformance = ClientCall::query()
            ->visibleForUser($user)
            ->whereDate('call_date', $today)
            ->select(
                'user_id',
                DB::raw("count(*) as total_calls"),
                DB::raw("SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as success_calls"),
                DB::raw("SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_calls")
            )
            ->groupBy('user_id')
            ->with('user:id,name')
            ->get();

        return view('clientcalls::livewire.widgets.agent-performance', [
            'performances' => $agentsPerformance
        ]);
    }
}
