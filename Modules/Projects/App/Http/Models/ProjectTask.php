<?php

namespace Modules\Projects\App\Http\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Morilog\Jalali\Jalalian;

class ProjectTask extends Model
{
    use SoftDeletes;

    protected $table = 'projects_tasks';

    protected $fillable = [
        'project_id',
        'phase_id',
        'status_id',
        'group_name',
        'title',
        'description',
        'due_date',
        'assigned_to',
        'manager_id',
        'created_by',
        'sort_order',
        'completed_at',
    ];

    protected $casts = [
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

    protected static function booted(): void
    {
        static::creating(function (ProjectTask $t) {
            if (is_null($t->status_id)) {
                $t->status_id = ProjectStatus::defaultFor('task')?->id;
            }
        });

        static::saved(function (ProjectTask $t) {
            $t->project?->refreshProgress();
        });

        static::deleted(function (ProjectTask $t) {
            $t->project?->refreshProgress();
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ProjectStatus::class, 'status_id');
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(ProjectPhase::class, 'phase_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(ProjectChecklistItem::class, 'task_id')->orderBy('sort_order');
    }

    public function timeLogs(): HasMany
    {
        return $this->hasMany(ProjectTimeLog::class, 'task_id')->latest();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ProjectTaskComment::class, 'task_id')->latest();
    }

    public function totalLoggedMinutes(): int
    {
        return (int) $this->timeLogs()->sum('duration_minutes');
    }

    public function formattedTotalTime(): string
    {
        $minutes = $this->totalLoggedMinutes();
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($hours > 0) {
            return sprintf('%d ساعت و %d دقیقه', $hours, $mins);
        }

        return sprintf('%d دقیقه', $mins);
    }

    public function totalChecklistCount(): int
    {
        return $this->checklistItems()->count();
    }

    public function completedChecklistCount(): int
    {
        return $this->checklistItems()->where('is_done', true)->count();
    }

    public function activeChecklistCount(): int
    {
        return $this->checklistItems()
            ->where(function ($q) {
                $q->whereNull('status_id')
                  ->orWhereDoesntHave('status', function ($sq) {
                      $sq->where('attributes->is_canceled', true)
                         ->orWhere('name', 'like', '%لغو%');
                  });
            })
            ->count();
    }

    public function checklistProgress(): int
    {
        $active = $this->activeChecklistCount();
        if ($active === 0) {
            return $this->totalChecklistCount() > 0 ? 100 : 0;
        }
        return (int) round(($this->completedChecklistCount() / $active) * 100);
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
        return (bool) ($this->status?->isCompleted() || str_contains($this->status?->name ?? '', 'تکمیل') || str_contains(strtolower($this->status?->name ?? ''), 'complete'));
    }

    public function isDelayed(): bool
    {
        return (bool) ($this->status?->isDelayed() || str_contains($this->status?->name ?? '', 'تعویق') || str_contains(strtolower($this->status?->name ?? ''), 'delay'));
    }

    public function isOverdue(): bool
    {
        if (!$this->due_date || $this->isCompleted() || $this->isCanceled()) {
            return false;
        }
        return $this->due_date->isPast() && !$this->due_date->isToday();
    }

    public function syncStatusFromChecklist(): ?ProjectStatus
    {
        // Canceled tasks are irreversible
        if ($this->isCanceled()) {
            return $this->status;
        }

        $total = $this->totalChecklistCount();
        $activeTotal = $this->activeChecklistCount();
        $done = $this->completedChecklistCount();
        $isOverdue = $this->due_date && $this->due_date->isPast() && !$this->due_date->isToday();

        $targetStatus = null;
        if ($activeTotal === 0) {
            if ($total > 0) {
                // All items are canceled, group is effectively finished
                $targetStatus = ProjectStatus::completedFor('task');
            } elseif ($isOverdue) {
                $targetStatus = ProjectStatus::delayedFor('task');
            } else {
                $targetStatus = ProjectStatus::queuedFor('task');
            }
        } elseif ($done >= $activeTotal) {
            $targetStatus = ProjectStatus::completedFor('task');
        } elseif ($isOverdue) {
            // Group due date has passed and not all items are completed -> status is delayed (تعویق)
            $targetStatus = ProjectStatus::delayedFor('task');
        } elseif ($done === 0) {
            $targetStatus = ProjectStatus::queuedFor('task');
        } else {
            $targetStatus = ProjectStatus::inProgressFor('task');
        }

        if ($targetStatus && $this->status_id !== $targetStatus->id) {
            $this->update([
                'status_id' => $targetStatus->id,
                'completed_at' => $targetStatus->isCompleted() ? now() : null,
            ]);
            $this->project?->refreshProgress();
        }

        return $this->fresh('status')->status;
    }
}
