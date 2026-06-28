<?php

namespace Modules\Tasks\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Reminders\Entities\Reminder;


class Task extends Model
{
    public $old_status_before_save;

    // انواع وظیفه (Task Type)
    public const TYPE_GENERAL    = 'GENERAL';    // وظیفه عمومی
    public const TYPE_FOLLOW_UP  = 'FOLLOW_UP';  // پیگیری (Follow-up)
    public const TYPE_SYSTEM     = 'SYSTEM';     // وظیفه سیستمی/خودکار (ایجاد شده از ماژول گردش کار)

    // وضعیت‌ها (Status)
    public const STATUS_TODO        = 'TODO';
    public const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    public const STATUS_DONE        = 'DONE';
    public const STATUS_CANCELED    = 'CANCELED';

    // اولویت‌ها (Priority)
    public const PRIORITY_LOW      = 'LOW';
    public const PRIORITY_MEDIUM   = 'MEDIUM';
    public const PRIORITY_HIGH     = 'HIGH';
    public const PRIORITY_CRITICAL = 'CRITICAL';

    public const RELATED_TYPE_USER   = 'USER';
    public const RELATED_TYPE_CLIENT = 'CLIENT';

    protected $table = 'tasks';

    protected $fillable = [
        'title',
        'description',
        'task_type',
        'assignee_id',
        'creator_id',
        'status',
        'priority',
        'due_at',
        'completed_at',
        'related_type',
        'related_id',
        'meta', // 👈 اضافه شود
    ];

    protected $casts = [
        'due_at'       => 'datetime',
        'completed_at' => 'datetime',
        'meta'         => 'array', // 👈
    ];


    /**
     * لیست انواع وظیفه برای استفاده در فرم‌ها (برچسب‌ها فارسی)
     */
    public static function typeOptions(): array
    {
        return [
            self::TYPE_GENERAL   => 'وظیفه عمومی',
            self::TYPE_FOLLOW_UP => 'پیگیری (Follow-up)',
            self::TYPE_SYSTEM    => 'سیستمی / خودکار (Workflow)',
        ];
    }

    /**
     * لیست وضعیت‌ها برای استفاده در فرم‌ها (برچسب‌ها فارسی)
     */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_TODO        => 'در صف انجام',
            self::STATUS_IN_PROGRESS => 'در حال انجام',
            self::STATUS_DONE        => 'انجام شده',
            self::STATUS_CANCELED    => 'لغو شده',
        ];
    }

    /**
     * لیست اولویت‌ها برای استفاده در فرم‌ها (برچسب‌ها فارسی)
     */
    public static function priorityOptions(): array
    {
        return [
            self::PRIORITY_LOW      => 'کم',
            self::PRIORITY_MEDIUM   => 'معمولی',
            self::PRIORITY_HIGH     => 'زیاد',
            self::PRIORITY_CRITICAL => 'بحرانی',
        ];
    }

    /**
     * ساخت Task سیستمی از طرف ماژول گردش‌کار
     * تمام فیلدهای لازم از payload گردش‌کار پر می‌شود.
     */
    public static function createFromWorkflow(array $payload): self
    {
        return static::create([
            'title'        => $payload['title']        ?? ($payload['task_name'] ?? 'وظیفه سیستمی'),
            'description'  => $payload['description']  ?? null,
            'task_type'    => self::TYPE_SYSTEM,
            'assignee_id'  => $payload['assignee_id']  ?? null,
            'creator_id'   => $payload['creator_id']   ?? null,
            'status'       => $payload['status']       ?? self::STATUS_TODO,
            'priority'     => $payload['priority']     ?? self::PRIORITY_MEDIUM,
            'due_at'       => $payload['due_at']       ?? null,
            'related_type' => $payload['related_type'] ?? null,
            'related_id'   => $payload['related_id']   ?? null,
        ]);
    }

    /**
     * هوک‌های مدل
     */
    protected static function booted(): void
    {
        // قبل از ذخیره وظیفه (ایجاد یا ویرایش)
        static::saving(function (Task $task) {
            if ($task->isDirty('status')) {
                $task->old_status_before_save = $task->getOriginal('status');
                if ($task->status === self::STATUS_DONE) {
                    $task->completed_at = now();
                } else {
                    $task->completed_at = null;
                }
            }
        });

        // بعد از ایجاد وظیفه
        static::created(function (Task $task) {
            $task->autoCreateReminderIfPossible();
            $task->processCompletionIfNeeded(true);
        });

        // بعد از بروزرسانی وظیفه
        static::updated(function (Task $task) {
            $task->syncRemindersOnUpdate();
            $task->processCompletionIfNeeded(false);
        });
    }

    public function processCompletionIfNeeded(bool $isCreation = false): void
    {
        $statusChangedToDone = $isCreation
            ? $this->status === self::STATUS_DONE
            : ($this->wasChanged('status') && $this->status === self::STATUS_DONE);

        if ($statusChangedToDone) {
            // First execute the system action if it exists
            if (isset($this->meta['_action_type'])) {
                $this->executeSystemAction();
            }

            $instanceId = $this->meta['workflow_instance_id'] ?? null;
            $nodeId = $this->meta['workflow_node_id'] ?? null;
            $autoAdvance = $this->meta['auto_advance'] ?? true;

            if (!$autoAdvance) {
                logger()->info("[Workflows] Task {$this->id} completed, but auto_advance is disabled in metadata. Pausing.");
                return;
            }

            if ($instanceId && class_exists(\Modules\Workflows\Services\WorkflowEngine::class) && class_exists(\Modules\Workflows\Entities\WorkflowInstance::class)) {
                if ($nodeId) {
                    // Check if there are other tasks for this same node of this workflow instance that are not DONE.
                    $pendingTasksCount = self::where('meta->workflow_instance_id', $instanceId)
                        ->where('meta->workflow_node_id', $nodeId)
                        ->where('status', '!=', self::STATUS_DONE)
                        ->count();

                    if ($pendingTasksCount > 0) {
                        logger()->info("[Workflows] Task {$this->id} completed, but {$pendingTasksCount} other tasks for node {$nodeId} are still pending.");
                        return;
                    }
                }

                $instance = \Modules\Workflows\Entities\WorkflowInstance::find($instanceId);
                if ($instance && $instance->status === \Modules\Workflows\Entities\WorkflowInstance::STATUS_ACTIVE) {
                    $engine = app(\Modules\Workflows\Services\WorkflowEngine::class);
                    $engine->advance($instance);
                }
            }
        }
    }

    protected function executeSystemAction(): void
    {
        $actionType = $this->meta['_action_type'] ?? null;
        $config = $this->meta['_node_config'] ?? [];

        if ($actionType === 'SMS') {
            if (class_exists('Modules\\Sms\\Services\\SmsManager')) {
                $to = null;
                $smsTarget = $config['sms_target'] ?? ($config['target'] ?? 'PATIENT');
                if ($smsTarget === 'CUSTOM_PHONE' || $smsTarget === 'CUSTOM') {
                    $to = $config['sms_phone'] ?? ($config['phone'] ?? null);
                } elseif ($smsTarget === 'SPECIFIC_USER') {
                    $targetUserId = $config['sms_target_user_id'] ?? ($config['target_user_id'] ?? null);
                    if ($targetUserId) {
                        $user = \App\Models\User::find($targetUserId);
                        $to = $user?->phone ?? $user?->email;
                    }
                } elseif ($smsTarget === 'APPOINTMENT_PROVIDER' || $smsTarget === 'PROVIDER') {
                    $instanceId = $this->meta['workflow_instance_id'] ?? null;
                    if ($instanceId && class_exists(\Modules\Workflows\Entities\WorkflowInstance::class)) {
                        $instance = \Modules\Workflows\Entities\WorkflowInstance::find($instanceId);
                        if ($instance && $instance->related_type === 'APPOINTMENT') {
                            $appt = \Modules\Booking\Entities\Appointment::find($instance->related_id);
                            if ($appt && $appt->provider) {
                                $to = $appt->provider->phone ?? $appt->provider->email;
                            }
                        }
                    }
                } elseif ($smsTarget === 'TREATMENT_PLAN_CLIENT') {
                    $clientId = $this->meta['related_client_ids'][0] ?? ($this->related_type === 'CLIENT' ? $this->related_id : null);
                    if ($clientId) {
                        $client = \Modules\Clients\Entities\Client::find($clientId);
                        if ($client) {
                            $to = $client->phone;
                        }
                    }
                } elseif ($smsTarget === 'TREATMENT_PLAN_CREATOR') {
                    $instanceId = $this->meta['workflow_instance_id'] ?? null;
                    if ($instanceId && class_exists(\Modules\Workflows\Entities\WorkflowInstance::class)) {
                        $instance = \Modules\Workflows\Entities\WorkflowInstance::find($instanceId);
                        if ($instance && $instance->related_type === 'TREATMENT_PLAN') {
                            $plan = \Modules\Booking\App\Models\TreatmentPlan::find($instance->related_id);
                            if ($plan && $plan->user) {
                                $to = $plan->user->phone ?? $plan->user->mobile;
                            }
                        }
                    }
                } elseif (str_starts_with($smsTarget, 'TREATMENT_PLAN_ROLE_')) {
                    $instanceId = $this->meta['workflow_instance_id'] ?? null;
                    if ($instanceId && class_exists(\Modules\Workflows\Entities\WorkflowInstance::class)) {
                        $instance = \Modules\Workflows\Entities\WorkflowInstance::find($instanceId);
                        if ($instance && $instance->related_type === 'TREATMENT_PLAN') {
                            $roleId = (int) str_replace('TREATMENT_PLAN_ROLE_', '', $smsTarget);
                            $engine = app(\Modules\Workflows\Services\WorkflowEngine::class);
                            $context = $engine->buildContextData($instance);
                            $assignedUsers = $context['assigned_users_by_role'][$roleId] ?? [];
                            if (!empty($assignedUsers)) {
                                $to = $assignedUsers[0]['phone'] ?? null;
                            }
                        }
                    }
                } else {
                    $clientId = $this->meta['related_client_ids'][0] ?? ($this->related_type === 'CLIENT' ? $this->related_id : null);
                    if ($clientId) {
                        $client = \Modules\Clients\Entities\Client::find($clientId);
                        if ($client) {
                            $to = $client->phone;
                        }
                    }
                }
                
                $message = $this->description ?: ($config['sms_message'] ?? 'پیامک سیستمی');
                
                if ($to) {
                    try {
                        $smsOptions = [
                            'type' => \Modules\Sms\Entities\SmsMessage::TYPE_SYSTEM,
                            'meta' => [
                                'workflow_id'          => $this->meta['workflow_id'] ?? null,
                                'workflow_name'        => $this->meta['workflow_name'] ?? null,
                                'workflow_instance_id' => $this->meta['workflow_instance_id'] ?? null,
                                'task_id'              => $this->id,
                            ]
                        ];
                        
                        $patternKey = $config['sms_pattern_key'] ?? ($config['pattern_key'] ?? null);
                        $resolvedParams = $config['resolved_params'] ?? [];
                        
                        if ($patternKey) {
                            app('Modules\\Sms\\Services\\SmsManager')->sendPattern($to, $patternKey, $resolvedParams, $smsOptions);
                            logger()->info("[Workflows] Pattern SMS sent via Task {$this->id} to {$to}");
                        } else {
                            app('Modules\\Sms\\Services\\SmsManager')->sendText($to, $message, $smsOptions);
                            logger()->info("[Workflows] SMS sent via Task {$this->id} to {$to}");
                        }
                    } catch (\Exception $e) {
                        logger()->error("[Workflows] SMS send failed via Task {$this->id}: " . $e->getMessage());
                    }
                } else {
                    logger()->warning("[Workflows] SMS skipped via Task {$this->id}: No target phone number resolved.");
                }
            }
        } elseif ($actionType === 'NOTIFICATION') {
            $notificationTarget = $config['notification_target'] ?? 'APPOINTMENT_CLIENT';
            $recipient = null;

            if ($notificationTarget === 'SPECIFIC_USER') {
                $targetUserId = $config['notification_target_user_id'] ?? null;
                if ($targetUserId) {
                    $recipient = \App\Models\User::find($targetUserId);
                }
            } elseif ($notificationTarget === 'APPOINTMENT_PROVIDER') {
                $instanceId = $this->meta['workflow_instance_id'] ?? null;
                if ($instanceId && class_exists(\Modules\Workflows\Entities\WorkflowInstance::class)) {
                    $instance = \Modules\Workflows\Entities\WorkflowInstance::find($instanceId);
                    if ($instance && $instance->related_type === 'APPOINTMENT') {
                        $appt = \Modules\Booking\Entities\Appointment::find($instance->related_id);
                        if ($appt && $appt->provider) {
                            $recipient = $appt->provider;
                        }
                    }
                }
            } elseif ($notificationTarget === 'TREATMENT_PLAN_CLIENT') {
                $clientId = $this->meta['related_client_ids'][0] ?? ($this->related_type === 'CLIENT' ? $this->related_id : null);
                if ($clientId) {
                    $recipient = \Modules\Clients\Entities\Client::find($clientId);
                }
            } elseif ($notificationTarget === 'TREATMENT_PLAN_CREATOR') {
                $instanceId = $this->meta['workflow_instance_id'] ?? null;
                if ($instanceId && class_exists(\Modules\Workflows\Entities\WorkflowInstance::class)) {
                    $instance = \Modules\Workflows\Entities\WorkflowInstance::find($instanceId);
                    if ($instance && $instance->related_type === 'TREATMENT_PLAN') {
                        $plan = \Modules\Booking\App\Models\TreatmentPlan::find($instance->related_id);
                        if ($plan && $plan->user) {
                            $recipient = $plan->user;
                        }
                    }
                }
            } elseif (str_starts_with($notificationTarget, 'TREATMENT_PLAN_ROLE_')) {
                $instanceId = $this->meta['workflow_instance_id'] ?? null;
                if ($instanceId && class_exists(\Modules\Workflows\Entities\WorkflowInstance::class)) {
                    $instance = \Modules\Workflows\Entities\WorkflowInstance::find($instanceId);
                    if ($instance && $instance->related_type === 'TREATMENT_PLAN') {
                        $roleId = (int) str_replace('TREATMENT_PLAN_ROLE_', '', $notificationTarget);
                        $engine = app(\Modules\Workflows\Services\WorkflowEngine::class);
                        $context = $engine->buildContextData($instance);
                        $assignedUsers = $context['assigned_user_models'][$roleId] ?? [];
                        if (!empty($assignedUsers)) {
                            $recipient = $assignedUsers[0];
                        }
                    }
                }
            } else {
                $clientId = $this->meta['related_client_ids'][0] ?? ($this->related_type === 'CLIENT' ? $this->related_id : null);
                if ($clientId) {
                    $recipient = \Modules\Clients\Entities\Client::find($clientId);
                }
            }

            if ($recipient) {
                $title = $this->title;
                $message = $this->description;
                try {
                    if (class_exists(\Modules\Workflows\Notifications\SystemNotification::class)) {
                        $recipient->notify(new \Modules\Workflows\Notifications\SystemNotification($title, $message));
                        logger()->info("[Workflows] Notification sent via Task {$this->id} to recipient");
                    }
                } catch (\Exception $e) {
                    logger()->error("[Workflows] Notification send failed via Task {$this->id}: " . $e->getMessage());
                }
            } else {
                logger()->warning("[Workflows] Notification skipped via Task {$this->id}: No target recipient resolved.");
            }
        }
    }


    /**
     * مسئول (assignee) وظیفه
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * ایجاد کننده (creator) وظیفه
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function getCreatorNameAttribute(): string
    {
        if ($this->task_type === self::TYPE_SYSTEM && $this->creator) {
            if ($this->creator->hasAnyRole(['admin', 'super-admin'])) {
                return 'سیستم';
            }
        }
        return $this->creator?->name ?? 'سیستم';
    }

    public function relatedClient()
    {
        return $this->belongsTo(\Modules\Clients\Entities\Client::class, 'related_id');
    }

    public function relatedUser()
    {
        return $this->belongsTo(User::class, 'related_id');
    }

    public function getAssigneeNameAttribute(): string
    {
        if ($this->assignee) {
            return $this->assignee->name;
        }

        $meta = $this->meta ?? [];
        if (($meta['assignee_mode'] ?? 'single_user') === 'by_roles') {
            $roleIds = $meta['assignee_role_ids'] ?? [];
            if (!empty($roleIds)) {
                if (in_array('__all__', $roleIds, true)) {
                    return 'همه نقش‌ها';
                }
                
                static $rolesCache = null;
                if ($rolesCache === null) {
                    if (class_exists(\Spatie\Permission\Models\Role::class)) {
                        $rolesCache = \Spatie\Permission\Models\Role::pluck('name', 'id')->all();
                    } else {
                        $rolesCache = [];
                    }
                }
                
                $names = [];
                foreach ($roleIds as $rid) {
                    if (isset($rolesCache[$rid])) {
                        $names[] = $rolesCache[$rid];
                    }
                }
                
                if (!empty($names)) {
                    return 'نقش: ' . implode('، ', $names);
                }
            }
        }

        return '—';
    }

    /**
     * اسکوپ مخصوص Follow-up ها
     */
    public function scopeFollowUps($query)
    {
        return $query->where('task_type', self::TYPE_FOLLOW_UP);
    }

    public function autoCreateReminderIfPossible(): void
    {
        // Allow system tasks to create reminders just like general tasks

        // اگر مسئول یا تاریخ سررسید مشخص نباشد، یادآوری نمی‌سازیم
        if (empty($this->assignee_id) || empty($this->due_at)) {
            logger()->warning('Task has no assignee or due_at, reminder will not be created.', [
                'task_id' => $this->id,
                'assignee_id' => $this->assignee_id,
                'due_at' => $this->due_at
            ]);
            return;
        }

        $reminderClass = 'Modules\\Reminders\\Entities\\Reminder';

        if (! class_exists($reminderClass)) {
            logger()->error('Reminder module is not installed or active.');
            // ماژول Reminders نصب/فعال نیست
            return;
        }

        try {
            $reminder = new $reminderClass();

            $reminder->user_id      = $this->assignee_id;
            $reminder->related_type = 'TASK';
            $reminder->related_id   = $this->id;
            $reminder->remind_at    = $this->due_at;
            $reminder->channel      = 'IN_APP';
            $reminder->message      = 'یادآوری انجام وظیفه: ' . ($this->title ?? 'بدون عنوان');

            if ($this->status === self::STATUS_DONE) {
                $reminder->status = $reminderClass::STATUS_DONE;
                $reminder->is_sent = true;
                $reminder->sent_at = now();
            } elseif ($this->status === self::STATUS_CANCELED) {
                $reminder->status = $reminderClass::STATUS_CANCELED;
                $reminder->is_sent = false;
            } else {
                $reminder->status = $reminderClass::STATUS_OPEN;
                $reminder->is_sent = false;
            }

            $reminder->save();
        } catch (\Throwable $e) {
            logger()->error('Task autoCreateReminderIfPossible failed', [
                'task_id' => $this->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class, 'related_id')
            ->where('related_type', 'TASK');
    }

    public function syncRemindersOnUpdate(): void
    {
        $reminderClass = Reminder::class;

        if (! class_exists($reminderClass)) {
            return;
        }

        $changes = $this->getChanges();
        $oldStatus = $this->old_status_before_save ?? $this->getOriginal('status');

        // اگر وضعیت وظیفه از DONE یا CANCELED به TODO یا IN_PROGRESS برگشت → یادآوری را مجدداً باز کن یا بساز
        if (
            array_key_exists('status', $changes)
            && in_array($this->status, [self::STATUS_TODO, self::STATUS_IN_PROGRESS])
            && in_array($oldStatus, [self::STATUS_DONE, self::STATUS_CANCELED])
        ) {
            if ($this->reminders()->exists()) {
                $lastReminder = $this->reminders()->orderByDesc('id')->first();
                if ($lastReminder) {
                    $lastReminder->update([
                        'status'  => Reminder::STATUS_OPEN,
                        'is_sent' => false,
                        'sent_at' => null,
                    ]);
                }
            } else {
                $this->autoCreateReminderIfPossible();
            }
        }

        // اگر هیچ یادآوری‌ای برای این Task ثبت نشده، دیگر تغییری روی یادآوری‌ها اعمال نمی‌کنیم
        if (! $this->reminders()->exists()) {
            return;
        }

        // اگر due_at یا assignee_id عوض شد → روی یادآوری‌ها اعمال کن
        $updateAll = [];

        if (array_key_exists('due_at', $changes)) {
            $updateAll['remind_at'] = $this->due_at;
        }

        if (array_key_exists('assignee_id', $changes)) {
            $updateAll['user_id'] = $this->assignee_id;
        }

        if (! empty($updateAll)) {
            $this->reminders()->update($updateAll);
        }

        // اگر وضعیت وظیفه DONE یا CANCELED شد → روی یادآوری‌های غیر DONE اعمال کن
        if (array_key_exists('status', $changes)) {
            if ($this->status === self::STATUS_DONE) {
                $this->reminders()
                    ->whereIn('status', [Reminder::STATUS_OPEN, Reminder::STATUS_ESCALATED])
                    ->update([
                        'status'  => Reminder::STATUS_DONE,
                        'is_sent' => true,
                        'sent_at' => now(),
                    ]);
            } elseif ($this->status === self::STATUS_CANCELED) {
                $this->reminders()
                    ->whereIn('status', [Reminder::STATUS_OPEN, Reminder::STATUS_ESCALATED])
                    ->update([
                        'status' => Reminder::STATUS_CANCELED,
                    ]);
            }
        }
    }

}
