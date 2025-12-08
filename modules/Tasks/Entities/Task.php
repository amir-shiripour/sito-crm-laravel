<?php

namespace Modules\Tasks\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        // بعد از ایجاد هر وظیفه، در صورت فعال بودن ماژول یادآوری‌ها،
        // یک Reminder برای مسئول ثبت می‌کنیم (به جز وظایف سیستمی که گردش‌کار خودش مدیریت می‌کند).
        static::created(function (Task $task) {
            $task->autoCreateReminderIfPossible();
        });
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

}
