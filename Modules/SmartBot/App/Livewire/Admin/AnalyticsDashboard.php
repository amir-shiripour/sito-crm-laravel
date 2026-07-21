<?php

declare(strict_types=1);

namespace Modules\SmartBot\App\Livewire\Admin;

use Livewire\Component;
use Modules\SmartBot\App\Models\BotSession;
use Modules\SmartBot\App\Models\BotMessage;
use Modules\SmartBot\App\Models\BotQuestion;
use Illuminate\Support\Facades\DB;

class AnalyticsDashboard extends Component
{
    public function mount()
    {
        if (!auth()->user()->can('smartbot.view')) {
            abort(403);
        }
    }

    public function getStatsProperty(): array
    {
        $totalSessions = BotSession::count();
        $totalMessages = BotMessage::count();
        
        $resolvedCount = BotMessage::where('role', 'bot')->where('resolved', true)->count();
        $botMessagesCount = BotMessage::where('role', 'bot')->count();
        $resolvedRate = $botMessagesCount > 0 ? round(($resolvedCount / $botMessagesCount) * 100, 1) : 0.0;

        // Unresolved queries list
        $unresolvedList = BotMessage::where('role', 'bot')
            ->where('resolved', false)
            ->select('content', DB::raw('count(*) as occurrences'), DB::raw('max(created_at) as last_seen'))
            ->groupBy('content')
            ->orderBy('occurrences', 'desc')
            ->limit(5)
            ->get()
            ->toArray();

        // FAQ - Top questions matched
        $topQuestions = BotMessage::where('role', 'bot')
            ->whereNotNull('question_id')
            ->join('bot_questions', 'bot_messages.question_id', '=', 'bot_questions.id')
            ->select('bot_questions.question_text', DB::raw('count(*) as count'))
            ->groupBy('bot_questions.question_text')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get()
            ->toArray();

        // Conversion analysis: How many cart additions in metadata?
        // We will store cart additions inside metadata: e.g. added_to_cart => true
        $sessionsWithCart = BotSession::where('metadata', 'like', '%"added_to_cart":true%')
            ->orWhere('metadata', 'like', '%added_to_cart%')
            ->count();
        
        $conversionRate = $totalSessions > 0 ? round(($sessionsWithCart / $totalSessions) * 100, 1) : 0.0;

        return [
            'total_sessions' => $totalSessions,
            'total_messages' => $totalMessages,
            'resolved_rate' => $resolvedRate,
            'unresolved_list' => $unresolvedList,
            'top_questions' => $topQuestions,
            'conversion_rate' => $conversionRate,
            'sessions_with_cart' => $sessionsWithCart,
        ];
    }

    public function render()
    {
        return view('smartbot::livewire.admin.analytics-dashboard')
            ->layout('layouts.user', ['title' => 'داشبورد آنالیتیکس دستیار هوشمند']);
    }
}
