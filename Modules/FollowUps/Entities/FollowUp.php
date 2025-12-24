<?php

namespace Modules\FollowUps\Entities;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Clients\Entities\Client;
use Modules\Tasks\Entities\Task;

class FollowUp extends Task
{
    protected static function booted(): void
    {
        // حتماً هوک‌های مدل Task (از جمله created → autoCreateReminderIfPossible) فعال شوند
        parent::booted();

        // فقط رکوردهای نوع FOLLOW_UP
        static::addGlobalScope('followups-only', function ($query) {
            $query->where('task_type', Task::TYPE_FOLLOW_UP);
        });

        // برای اطمینان، قبل از ساخت هر FollowUp، نوع آن را FOLLOW_UP می‌کنیم
        static::creating(function (Task $task) {
            if ($task->task_type !== Task::TYPE_FOLLOW_UP) {
                $task->task_type = Task::TYPE_FOLLOW_UP;
            }
        });
    }

    /**
     * مشتری مرتبط با این پیگیری (وقتی related_type = CLIENT باشد)
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'related_id');
    }
}
