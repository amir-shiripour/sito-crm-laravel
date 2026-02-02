<?php

namespace Modules\Reminders\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Tasks\Entities\Task;
use Modules\FollowUps\Entities\FollowUp;
use Illuminate\Support\Facades\Route;
use Modules\Clients\Entities\Client;


class Reminder extends Model
{
    // کانال‌ها
    public const CHANNEL_IN_APP = 'IN_APP';
    public const CHANNEL_EMAIL  = 'EMAIL';
    public const CHANNEL_SMS    = 'SMS';
    public const CHANNEL_PUSH   = 'PUSH';
    public const CHANNEL_WORKFLOW = 'WORKFLOW';

    // وضعیت‌ها
    public const STATUS_OPEN     = 'OPEN';
    public const STATUS_DONE     = 'DONE';
    public const STATUS_CANCELED = 'CANCELED';

    protected $table = 'reminders';

    protected $fillable = [
        'user_id',
        'related_type',
        'related_id',
        'remind_at',
        'channel',
        'message',
        'is_sent',
        'sent_at',
        'status',
    ];

    protected $casts = [
        'remind_at' => 'datetime',
        'sent_at'   => 'datetime',
        'is_sent'   => 'bool',
    ];

    /* ---------------- روابط ---------------- */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * وظیفه‌ای که این یادآوری برای آن ثبت شده (هم برای وظیفه و هم پیگیری)
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'related_id');
    }

    /**
     * پیگیری (FollowUp) متناظر، وقتی task_type = FOLLOW_UP
     */
    public function followUp(): BelongsTo
    {
        return $this->belongsTo(FollowUp::class, 'related_id');
    }

    /* ---------------- اسکوپ‌ها ---------------- */

    /**
     * محدود کردن نمایش بر اساس سطح دسترسی:
     * - اگر reminders.manage یا reminders.view داشته باشد -> همه
     * - اگر فقط reminders.view.own داشته باشد -> فقط خودش
     */
    public function scopeVisibleForUser(Builder $query, User $user): Builder
    {
        if ($user->can('reminders.manage') || $user->can('reminders.view')) {
            return $query;
        }

        // فقط یادآوری‌های خودش
        return $query->where('user_id', $user->id);
    }

    /**
     * فقط یادآوری‌های باز (OPEN)
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    /**
     * یادآوری‌هایی که روی TASK ساخته شده‌اند
     */
    public function scopeForTasks(Builder $query): Builder
    {
        return $query->where('related_type', 'TASK');
    }

    /**
     * فقط یادآوری‌های مربوط به پیگیری‌ها (FollowUp)
     */
    public function scopeForFollowUps(Builder $query): Builder
    {
        return $query->forTasks()->whereHas('task', function (Builder $q) {
            $q->where('task_type', Task::TYPE_FOLLOW_UP);
        });
    }

    /**
     * فقط یادآوری‌های مربوط به وظایف عمومی (غیر FollowUp)
     */
    public function scopeForGeneralTasks(Builder $query): Builder
    {
        return $query->forTasks()->whereHas('task', function (Builder $q) {
            $q->where('task_type', '!=', Task::TYPE_FOLLOW_UP);
        });
    }

    /* ---------------- استاتیک‌ها / آپشن‌ها ---------------- */

    public static function statusOptions(): array
    {
        return [
            self::STATUS_OPEN     => 'باز',
            self::STATUS_DONE     => 'انجام شده',
            self::STATUS_CANCELED => 'لغو شده',
        ];
    }

    /**
     * وزن عددی اولویت برای مرتب‌سازی (بیشتر = مهم‌تر)
     */
    public static function priorityWeight(?string $priority): int
    {
        return match ($priority) {
            Task::PRIORITY_CRITICAL => 4,
            Task::PRIORITY_HIGH     => 3,
            Task::PRIORITY_MEDIUM   => 2,
            Task::PRIORITY_LOW      => 1,
            default                 => 0,
        };
    }

    /* ---------------- موجودیت مرتبط (Dynamic) ---------------- */

    /**
     * موجودیت مرتبط اصلی (الآن فقط TASK، در آینده CLIENT, INVOICE, ...)
     */
    public function related()
    {
        return match ($this->related_type) {
            'TASK' => $this->task,   // هم وظیفه عمومی هم FollowUp
            'CLIENT' => $this->client ?? null, // برای آینده اگر خواستی
            default => null,
        };
    }

    /**
     * عنوان نمایش برای موجودیت مرتبط
     */
    public function relatedTitle(): string
    {
        if ($this->related_type === 'TASK' && $this->task) {
            return $this->task->title ?? 'وظیفه بدون عنوان';
        }

        if ($this->related_type === 'CLIENT' && isset($this->client)) {
            return $this->client->full_name ?? $this->client->username ?? 'مشتری';
        }

        return $this->message ?: 'یادآوری #' . $this->id;
    }

    /**
     * نام روت و مدل برای لینک دادن به موجودیت مرتبط
     */
    public function relatedRoute(): ?array
    {
        if ($this->related_type === 'TASK' && $this->task) {
            // اگر این Task از نوع Follow-up است → صفحه‌ی پیگیری
            if ($this->task->task_type === Task::TYPE_FOLLOW_UP) {
                return ['user.followups.show', $this->task];
            }

            // در غیر این صورت → صفحه‌ی وظیفه
            return ['user.tasks.show', $this->task];
        }

        // مثال برای آینده (در صورت نیاز)
        // if ($this->related_type === 'CLIENT' && $this->client) {
        //     return ['user.clients.show', $this->client];
        // }

        return null;
    }

    public function relatedUrl(): ?string
    {
        $route = $this->relatedRoute();

        if (! $route) {
            return null;
        }

        try {
            return route($route[0], $route[1]);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * اولویت وظیفه‌ی مرتبط (برای سورت)
     */
    public function relatedPriorityWeight(): int
    {
        if ($this->related_type === 'TASK' && $this->task) {
            return self::priorityWeight($this->task->priority);
        }

        return 0;
    }

    /* --- اگر خواستی در آینده: رابطه‌ی client --- */

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'related_id')
            ->where('related_type', 'CLIENT');
    }


    /* ---------------- متدهای کمکی ---------------- */

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isDone(): bool
    {
        return $this->status === self::STATUS_DONE;
    }

    public function isCanceled(): bool
    {
        return $this->status === self::STATUS_CANCELED;
    }

    public function canBeEditedBy(User $user): bool
    {
        if (! $user->can('reminders.edit')) {
            return false;
        }

        if ($user->can('reminders.manage')) {
            return true;
        }

        return $this->user_id === $user->id;
    }

    public function canBeDeletedBy(User $user): bool
    {
        if (! $user->can('reminders.delete')) {
            return false;
        }

        if ($user->can('reminders.manage')) {
            return true;
        }

        return $this->user_id === $user->id;
    }

    public function canChangeStatus(User $user): bool
    {
        // همان منطق edit
        return $this->canBeEditedBy($user);
    }
}
