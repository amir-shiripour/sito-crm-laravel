<?php

declare(strict_types=1);

namespace Modules\Sales\App\Services;

use Modules\Tasks\Entities\Task;
use Illuminate\Support\Facades\Log;

final class SalesAutomationService
{
    /**
     * پردازش تماس ثبت‌شده و ایجاد تسک پیگیری خودکار در صورت نیاز
     */
    public function handleCallLogged(mixed $call): void
    {
        try {
            $isClientCall = get_class($call) === 'Modules\ClientCalls\Entities\ClientCall';
            $isSalesCall = get_class($call) === 'Modules\Sales\App\Models\SalesCall';

            if (!$isClientCall && !$isSalesCall) {
                return;
            }

            // تشخیص ناموفق بودن تماس
            $isUnsuccessful = false;
            if ($isClientCall) {
                // در کلاینت‌کالز اگر موفقیت غیر از 'done' باشد
                $isUnsuccessful = $call->status !== 'done';
            } elseif ($isSalesCall) {
                // در سیلزکالز اگر وضعیت در آرایه زیر باشد
                $isUnsuccessful = in_array($call->status, ['no_answer', 'busy', 'failed', 'cancelled'], true);
            }

            $nextAction = $call->next_action ?? null;
            $nextActionDate = $call->next_action_date ?? null;

            // تشخیص وجود پرونده فروش (Deal) مرتبط
            $dealId = $call->deal_id ?? null;
            $clientId = $call->client_id ?? null;

            $relatedType = $dealId ? 'DEAL' : 'CLIENT';
            $relatedId = $dealId ?: $clientId;

            if (!$relatedId) {
                return;
            }

            $assigneeId = $call->user_id ?? auth()->id();
            
            if ($nextAction && $nextActionDate) {
                // سناریو ۱: کارشناس اقدام بعدی و تاریخ را ثبت کرده است
                Task::create([
                    'title' => $nextAction,
                    'description' => 'پیگیری خودکار ثبت شده بر اساس اقدام تعریف شده در تماس قبلی.',
                    'task_type' => Task::TYPE_FOLLOW_UP,
                    'assignee_id' => $assigneeId,
                    'creator_id' => auth()->id() ?: $assigneeId,
                    'status' => Task::STATUS_TODO,
                    'priority' => Task::PRIORITY_HIGH,
                    'due_at' => $nextActionDate,
                    'related_type' => $relatedType,
                    'related_id' => $relatedId,
                ]);

                Log::info("[Sales Automation] Auto-scheduled followup task for planned action: {$nextAction} on {$nextActionDate}");

            } elseif ($isUnsuccessful) {
                // سناریو ۲: تماس ناموفق بوده و کارشناس اقدامی مشخص نکرده است -> ایجاد تسک پیش‌فرض فردا
                $title = 'پیگیری تماس ناموفق';
                $resultText = $call->result ?: $call->reason ?: $call->status;
                $description = "تماس قبلی ناموفق بود ({$resultText}). لطفا مجددا تماس حاصل نمایید.";
                $dueAt = now()->addDay()->startOfDay()->addHours(9); // فردا ساعت ۹ صبح

                Task::create([
                    'title' => $title,
                    'description' => $description,
                    'task_type' => Task::TYPE_FOLLOW_UP,
                    'assignee_id' => $assigneeId,
                    'creator_id' => auth()->id() ?: $assigneeId,
                    'status' => Task::STATUS_TODO,
                    'priority' => Task::PRIORITY_MEDIUM,
                    'due_at' => $dueAt,
                    'related_type' => $relatedType,
                    'related_id' => $relatedId,
                ]);

                Log::info("[Sales Automation] Auto-scheduled failure followup task for assignee ID: {$assigneeId}");
            }
        } catch (\Throwable $e) {
            Log::error("[Sales Automation Error] Failed to handle call automation: " . $e->getMessage());
        }
    }
}
