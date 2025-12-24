<?php

namespace Modules\Workflows\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Morilog\Jalali\Jalalian;
use Modules\Workflows\Entities\Workflow;
use Modules\Workflows\Entities\WorkflowStage;
use Modules\Workflows\Entities\WorkflowAction;
use Modules\Workflows\Entities\WorkflowInstance;
use Modules\Workflows\Entities\WorkflowLog;

class WorkflowEngine
{
    public function start(string $workflowKey, string $relatedType, int $relatedId): ?WorkflowInstance
    {
        Log::info("[Workflows] Request to start workflow: '{$workflowKey}' for {$relatedType}:{$relatedId}");

        $workflow = Workflow::where('key', $workflowKey)->where('is_active', true)->first();
        if (! $workflow) {
            Log::warning("[Workflows] Workflow not found or inactive: '{$workflowKey}'");
            return null;
        }

        $initialStage = $workflow->stages()->where('is_initial', true)->orderBy('sort_order')->first()
            ?: $workflow->stages()->orderBy('sort_order')->first();

        if (! $initialStage) {
            Log::warning("[Workflows] No initial stage found for workflow: '{$workflow->name}'");
            return null;
        }

        return DB::transaction(function () use ($workflow, $initialStage, $relatedType, $relatedId) {
            $instance = WorkflowInstance::create([
                'workflow_id'      => $workflow->id,
                'related_type'     => $relatedType,
                'related_id'       => $relatedId,
                'current_stage_id' => $initialStage->id,
                'status'           => WorkflowInstance::STATUS_ACTIVE,
                'started_at'       => now(),
                'created_by'       => Auth::id(),
            ]);

            Log::info("[Workflows] Instance created: {$instance->id}. Running initial stage actions.");
            $this->runStageActions($instance, $initialStage);

            return $instance;
        });
    }

    public function moveToStage(WorkflowInstance $instance, WorkflowStage $stage): void
    {
        DB::transaction(function () use ($instance, $stage) {
            $instance->current_stage_id = $stage->id;
            $instance->save();

            $this->runStageActions($instance, $stage);
        });
    }

    protected function runStageActions(WorkflowInstance $instance, WorkflowStage $stage): void
    {
        $actions = $stage->actions()->orderBy('sort_order')->get();
        Log::info("[Workflows] Found " . $actions->count() . " actions for stage: {$stage->name}");

        $context = $this->buildContextData($instance);

        foreach ($actions as $action) {
            try {
                $result = $this->runAction($instance, $stage, $action, $context);

                WorkflowLog::create([
                    'instance_id' => $instance->id,
                    'stage_id'    => $stage->id,
                    'action_type' => $action->action_type,
                    'data'        => ['config' => $action->config, 'result' => $result],
                    'run_at'      => now(),
                    'user_id'     => Auth::id(),
                ]);
            } catch (\Throwable $e) {
                Log::error('[Workflows] action failed', [
                    'instance_id' => $instance->id,
                    'stage_id'    => $stage->id,
                    'action_id'   => $action->id,
                    'error'       => $e->getMessage(),
                    'trace'       => $e->getTraceAsString(),
                ]);
            }
        }
    }

    protected function runAction(WorkflowInstance $instance, WorkflowStage $stage, WorkflowAction $action, array $context): array
    {
        $config = $action->config ?? [];
        $result = ['status' => 'skipped'];
        $deps = config('workflows.dependencies', []);

        Log::info("[Workflows] Running action ID: {$action->id} Type: {$action->action_type}", ['config' => $config]);

        // Base date for offsets: appointment start time if available, otherwise workflow start time
        $baseDate = $context['appointment']->start_at_utc ?? $instance->started_at ?? now();

        // Resolve target user/assignee
        $targetUserId = Auth::id();
        $assigneeTarget = $config['assignee_target'] ?? 'CURRENT_USER';

        if ($assigneeTarget === 'APPOINTMENT_PROVIDER' && isset($context['appointment'])) {
            $targetUserId = $context['appointment']->provider_user_id ?? $targetUserId;
            Log::info("[Workflows] Assignee resolved to PROVIDER: " . ($targetUserId ?? 'NULL'));
        } elseif ($assigneeTarget === 'SPECIFIC_USER' && !empty($config['assignee_id'])) {
            $targetUserId = $config['assignee_id'];
            Log::info("[Workflows] Assignee resolved to SPECIFIC_USER: " . ($targetUserId ?? 'NULL'));
        } else {
            Log::info("[Workflows] Assignee resolved to CURRENT_USER (or default): " . ($targetUserId ?? 'NULL'));
        }

        switch ($action->action_type) {
            case WorkflowAction::TYPE_CREATE_TASK:
            case WorkflowAction::TYPE_CREATE_FOLLOWUP:
                $isTask = $action->action_type === WorkflowAction::TYPE_CREATE_TASK;
                $module = $isTask ? ($deps['tasks'] ?? null) : ($deps['followups'] ?? null);
                $class = $isTask ? 'Modules\\Tasks\\Entities\\Task' : 'Modules\\FollowUps\\Entities\\FollowUp';

                if ($this->isModuleEnabled($module) && class_exists($class)) {
                    $Model = $class;

                    $dueAt = $this->calcOffsetDate($config['offset_days'] ?? 0, $baseDate);
                    $title = $this->renderTemplate($config['title'] ?? $stage->name, $context);
                    $description = $this->renderTemplate($config['description'] ?? '', $context);

                    Log::info("[Workflows] Creating Task/FollowUp. Title: $title, Assignee: $targetUserId");

                    try {
                        $task = $Model::create([
                            'title'        => $title,
                            'description'  => $description,
                            'task_type'    => $isTask ? ($config['task_type'] ?? 'GENERAL') : 'FOLLOW_UP',
                            'assignee_id'  => $targetUserId,
                            'creator_id'   => Auth::id(),
                            'status'       => $config['status'] ?? 'TODO',
                            'priority'     => $config['priority'] ?? ($isTask ? 'MEDIUM' : 'HIGH'),
                            'due_at'       => $dueAt,
                            'related_type' => $instance->related_type,
                            'related_id'   => $instance->related_id,
                        ]);
                        Log::info("[Workflows] Task created successfully. ID: {$task->id}");
                        $result = ['status' => 'created', 'task_id' => $task->id];
                    } catch (\Exception $e) {
                        Log::error("[Workflows] Task creation threw exception: " . $e->getMessage());
                        throw $e;
                    }
                } else {
                    Log::warning("[Workflows] Module $module not enabled or class $class not found.");
                }
                break;

            case WorkflowAction::TYPE_CREATE_REMINDER:
                if ($this->isModuleEnabled($deps['reminders'] ?? null) && class_exists('Modules\\Reminders\\Entities\\Reminder')) {
                    $Reminder = 'Modules\\Reminders\\Entities\\Reminder';

                    $remindAt = $this->calcOffsetMinutes($config['offset_minutes'] ?? 0, $baseDate);
                    $message = $this->renderTemplate($config['message'] ?? ('یادآوری: ' . $stage->name), $context);

                    $reminder = $Reminder::create([
                        'user_id'      => $targetUserId,
                        'related_type' => 'WORKFLOW_INSTANCE',
                        'related_id'   => $instance->id,
                        'remind_at'    => $remindAt,
                        'channel'      => $config['channel'] ?? config('workflows.default_reminder_channel', 'IN_APP'),
                        'message'      => $message,
                        'is_sent'      => false,
                    ]);

                    $result = ['status' => 'created', 'reminder_id' => $reminder->id];
                }
                break;

            case WorkflowAction::TYPE_SEND_NOTIFICATION:
                $message = $this->renderTemplate($config['message'] ?? ('اجرای مرحله '.$stage->name), $context);
                Log::info('[Workflows] notification', [
                    'instance_id' => $instance->id,
                    'message'     => $message,
                ]);
                $result = ['status' => 'logged'];
                break;

            case WorkflowAction::TYPE_SEND_SMS:
                if ($this->isModuleEnabled($deps['sms'] ?? null) && class_exists('Modules\\Sms\\Services\\SmsManager')) {
                    $to = $this->resolveTargetPhone($config, $context);

                    if ($to) {
                        $Sms = app(\Modules\Sms\Services\SmsManager::class);
                        $params = $this->resolveSmsParams($config['params'] ?? [], $context);
                        $message = $this->renderTemplate($config['message'] ?? '', $context, $params);

                        $options = [
                            'type' => \Modules\Sms\Entities\SmsMessage::TYPE_SYSTEM,
                            'related_type' => $instance->related_type,
                            'related_id' => $instance->related_id,
                        ];

                        if (!empty($config['offset_minutes'])) {
                            $options['scheduled_at'] = $this->calcOffsetMinutes((int) $config['offset_minutes'], $baseDate);
                        }

                        if (!empty($config['pattern_key'])) {
                            $sms = $Sms->sendPattern($to, $config['pattern_key'], $params, $options);
                            $result = ['status' => 'pattern_sent', 'sms_id' => $sms->id, 'to' => $to];
                        } else {
                            $sms = $Sms->sendText($to, $message, $options);
                            $result = ['status' => 'text_sent', 'sms_id' => $sms->id, 'to' => $to];
                        }
                    } else {
                        $result = ['status' => 'skipped', 'reason' => 'missing_phone'];
                    }
                }
                break;
        }

        return $result;
    }

    protected function isModuleEnabled(?string $moduleName): bool
    {
        if (!$moduleName) return false;
        if (class_exists('Nwidart\\Modules\\Facades\\Module')) {
            return \Nwidart\Modules\Facades\Module::has($moduleName) && \Nwidart\Modules\Facades\Module::isEnabled($moduleName);
        }
        return true;
    }

    protected function calcOffsetDate(int $days, $base)
    {
        $base = $base ? \Illuminate\Support\Carbon::parse($base) : now();
        return $days ? $base->copy()->addDays($days) : $base;
    }

    protected function calcOffsetMinutes(int $minutes, $base)
    {
        $base = $base ? \Illuminate\Support\Carbon::parse($base) : now();
        return $minutes ? $base->copy()->addMinutes($minutes) : $base;
    }

    protected function buildContextData(WorkflowInstance $instance): array
    {
        $data = ['tokens' => []];

        if ($instance->related_type === 'APPOINTMENT' && class_exists('Modules\\Booking\\Entities\\Appointment')) {
            $appt = \Modules\Booking\Entities\Appointment::query()
                ->with(['client', 'service', 'provider'])
                ->find($instance->related_id);

            if ($appt) {
                $scheduleTz = config('booking.timezones.display_default', 'Asia/Tehran');
                $dateJalali = $appt->start_at_utc ? Jalalian::fromDateTime($appt->start_at_utc->copy()->timezone($scheduleTz)) : null;

                $data['appointment'] = $appt;
                $data['tokens'] = [
                    'client_name' => $appt->client?->full_name,
                    'client_phone' => $appt->client?->phone,
                    'service_name' => $appt->service?->name,
                    'provider_name' => $appt->provider?->name,
                    'appointment_date_jalali' => $dateJalali?->format('Y/m/d'),
                    'appointment_time_jalali' => $dateJalali?->format('H:i'),
                    'appointment_datetime_jalali' => $dateJalali?->format('Y/m/d H:i'),
                ];
            }
        }

        return $data;
    }

    protected function resolveTargetPhone(array $config, array $context): ?string
    {
        $target = $config['target'] ?? 'APPOINTMENT_CLIENT';
        if ($target === 'APPOINTMENT_CLIENT') {
            return $context['tokens']['client_phone'] ?? null;
        }
        if ($target === 'CUSTOM_PHONE') {
            return $config['phone'] ?? null;
        }
        return null;
    }

    protected function resolveSmsParams(array $paramKeys, array $context): array
    {
        $tokens = $context['tokens'] ?? [];
        $params = [];
        foreach ($paramKeys as $key) {
            $params[] = $tokens[$key] ?? '';
        }
        return $params;
    }

    protected function renderTemplate(string $template, array $context, array $indexedParams = []): string
    {
        $tokens = $context['tokens'] ?? [];

        // Replace indexed placeholders {0}, {1}, ...
        if (!empty($indexedParams)) {
            foreach ($indexedParams as $idx => $value) {
                $template = str_replace('{' . $idx . '}', (string) $value, $template);
            }
        }

        // Replace named placeholders like {client_name}
        foreach ($tokens as $key => $value) {
            $template = str_replace('{' . $key . '}', (string) $value, $template);
        }

        return $template;
    }
}
