<?php

namespace Modules\Tasks\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Reminders\Entities\Reminder;


class Task extends Model
{
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
        });

        // بعد از بروزرسانی وظیفه
        static::updated(function (Task $task) {
            $task->syncRemindersOnUpdate();
            $task->advanceWorkflowIfDone();
        });
    }

    public function advanceWorkflowIfDone(): void
    {
        if ($this->wasChanged('status') && $this->status === self::STATUS_DONE) {
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
        // برای وظایف سیستمی، خودِ ماژول گردش‌کار Reminderهای لازم را می‌سازد
        if ($this->task_type === self::TYPE_SYSTEM) {
            return;
        }

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
            $reminder->is_sent      = false;

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

        // اگر هیچ یادآوری‌ای برای این Task ثبت نشده، بیخیال
        if (! $this->reminders()->exists()) {
            return;
        }

        $dirty = $this->getDirty();

        // اگر due_at یا assignee_id عوض شد → روی یادآوری‌ها اعمال کن
        $updateAll = [];

        if (array_key_exists('due_at', $dirty) && $this->due_at) {
            $updateAll['remind_at'] = $this->due_at;
        }

        if (array_key_exists('assignee_id', $dirty) && $this->assignee_id) {
            $updateAll['user_id'] = $this->assignee_id;
        }

        if (! empty($updateAll)) {
            $this->reminders()->update($updateAll);
        }

        // اگر وضعیت وظیفه DONE یا CANCELED شد → فقط روی OPENها اعمال کن
        if (array_key_exists('status', $dirty)) {
            if ($this->status === self::STATUS_DONE) {
                $this->reminders()
                    ->where('status', Reminder::STATUS_OPEN)
                    ->update([
                        'status'  => Reminder::STATUS_DONE,
                        'is_sent' => true,
                        'sent_at' => now(),
                    ]);
            } elseif ($this->status === self::STATUS_CANCELED) {
                $this->reminders()
                    ->where('status', Reminder::STATUS_OPEN)
                    ->update([
                        'status' => Reminder::STATUS_CANCELED,
                    ]);
            } elseif (in_array($this->status, [self::STATUS_TODO, self::STATUS_IN_PROGRESS])) {
                $oldStatus = $this->getOriginal('status');
                if (in_array($oldStatus, [self::STATUS_DONE, self::STATUS_CANCELED])) {
                    if (! $this->reminders()->where('status', Reminder::STATUS_OPEN)->exists()) {
                        // Reopen the most recent reminder or create a new one
                        $lastReminder = $this->reminders()->orderByDesc('id')->first();
                        if ($lastReminder) {
                            $lastReminder->update([
                                'status' => Reminder::STATUS_OPEN,
                                'is_sent' => false,
                                'sent_at' => null,
                            ]);
                        } else {
                            $this->autoCreateReminderIfPossible();
                        }
                    }
                }
            }
        }
    }

}
