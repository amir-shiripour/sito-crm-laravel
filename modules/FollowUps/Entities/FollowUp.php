<?php

namespace Modules\FollowUps\Entities;

use Modules\Tasks\Entities\Task;

class FollowUp extends Task
{
    protected static function booted(): void
    {
        static::addGlobalScope('followups-only', function ($query) {
            $query->where('task_type', Task::TYPE_FOLLOW_UP);
        });
    }
}
