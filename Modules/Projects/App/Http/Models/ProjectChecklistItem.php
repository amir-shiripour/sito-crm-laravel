<?php

namespace Modules\Projects\App\Http\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Morilog\Jalali\Jalalian;

class ProjectChecklistItem extends Model
{
    protected $table = 'projects_checklist_items';

    protected $fillable = [
        'task_id',
        'status_id',
        'title',
        'description',
        'priority',
        'assigned_to',
        'created_by',
        'is_done',
        'due_date',
        'sort_order',
        'completed_at',
    ];

    protected $casts = [
        'is_done' => 'boolean',
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'due_date_jalali',
    ];

    public function getDueDateJalaliAttribute(): ?string
    {
        if (!$this->due_date) {
            return null;
        }
        try {
            if (class_exists(Jalalian::class)) {
                return Jalalian::fromCarbon($this->due_date)->format('Y/m/d');
            }
            if (function_exists('jdate')) {
                return jdate($this->due_date)->format('Y/m/d');
            }
        } catch (\Throwable) {
            return null;
        }
        return null;
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'task_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ProjectStatus::class, 'status_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'projects_checklist_item_users', 'checklist_item_id', 'user_id')
            ->withPivot(['is_done', 'completed_at'])
            ->withTimestamps();
    }

    public function isCompletedBy(?int $userId): bool
    {
        if (!$userId) {
            return (bool) $this->is_done;
        }

        $assignees = $this->relationLoaded('assignees') ? $this->assignees : $this->assignees()->get();
        $assignee = $assignees->firstWhere('id', $userId);
        if ($assignee) {
            return (bool) $assignee->pivot->is_done;
        }

        return (bool) $this->is_done;
    }

    public function areAllAssigneesDone(): bool
    {
        $assignees = $this->relationLoaded('assignees') ? $this->assignees : $this->assignees()->get();
        if ($assignees->isEmpty()) {
            return (bool) $this->is_done;
        }

        return $assignees->every(fn($u) => (bool)$u->pivot->is_done);
    }

    public function assigneesCompletedCount(): int
    {
        $assignees = $this->relationLoaded('assignees') ? $this->assignees : $this->assignees()->get();
        return $assignees->filter(fn($u) => (bool)$u->pivot->is_done)->count();
    }

    public function assigneesTotalCount(): int
    {
        $assignees = $this->relationLoaded('assignees') ? $this->assignees : $this->assignees()->get();
        return $assignees->count();
    }

    public function syncAssignees(array|int $userIds): void
    {
        $userIds = is_array($userIds) ? $userIds : [$userIds];
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));

        $currentPivot = $this->assignees()->get()->keyBy('id');
        $syncData = [];

        foreach ($userIds as $uid) {
            if ($currentPivot->has($uid)) {
                $existing = $currentPivot->get($uid);
                $syncData[$uid] = [
                    'is_done' => (bool)$existing->pivot->is_done,
                    'completed_at' => $existing->pivot->completed_at,
                ];
            } else {
                $syncData[$uid] = [
                    'is_done' => (bool)$this->is_done,
                    'completed_at' => $this->is_done ? ($this->completed_at ?? now()) : null,
                ];
            }
        }

        $this->assignees()->sync($syncData);

        $firstAssignee = !empty($userIds) ? $userIds[0] : null;
        $this->update(['assigned_to' => $firstAssignee]);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ProjectChecklistComment::class, 'checklist_item_id')->latest();
    }

    public function isOverdue(): bool
    {
        if (!$this->due_date || $this->is_done || $this->isCanceled()) {
            return false;
        }
        return $this->due_date->isPast() && !$this->due_date->isToday();
    }

    public function isCanceled(): bool
    {
        if ($this->status?->isCanceled()) {
            return true;
        }
        $name = $this->status?->name ?? '';
        return str_contains($name, 'لغو') || str_contains(strtolower($name), 'cancel');
    }

    public function isCompleted(): bool
    {
        if ($this->isCanceled()) {
            return false;
        }
        return (bool) $this->is_done;
    }

    public function isDelayed(): bool
    {
        if ($this->isCanceled() || $this->is_done) {
            return false;
        }
        return (bool) ($this->status?->isDelayed() || $this->isOverdue());
    }
}
