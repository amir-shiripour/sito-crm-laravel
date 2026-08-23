<?php

namespace Modules\Projects\App\Http\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Clients\Entities\Client;

class Project extends Model
{
    use SoftDeletes;

    protected $table = 'projects';

    protected $fillable = [
        'title',
        'code',
        'description',
        'category_id',
        'client_id',
        'status_id',
        'start_date',
        'end_date',
        'created_by',
        'progress',
        'is_template',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'progress' => 'integer',
        'is_template' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Project $p) {
            if (empty($p->code)) {
                $p->code = static::generateNextCode();
            }
            if (is_null($p->status_id)) {
                $defaultStatusId = ProjectSetting::get('projects_default_status_id');
                $p->status_id = $defaultStatusId ?: ProjectStatus::defaultFor('project')?->id;
            }
            if (is_null($p->category_id)) {
                $defaultCatId = ProjectSetting::get('projects_default_category_id');
                if ($defaultCatId) {
                    $p->category_id = $defaultCatId;
                }
            }
        });
    }

    public static function generateNextCode(): string
    {
        $prefix = ProjectSetting::get('projects_code_prefix', 'PRJ-');
        $middle = ProjectSetting::get('projects_code_middle', now()->format('Y'));
        $suffix = ProjectSetting::get('projects_code_suffix', '');
        $padding = ProjectSetting::getInt('projects_code_padding', 4);

        $count = static::withTrashed()->count() + 1;
        $middlePart = !empty($middle) ? $middle . '-' : '';

        return $prefix . $middlePart . str_pad($count, max(1, $padding), '0', STR_PAD_LEFT) . $suffix;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'category_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ProjectStatus::class, 'status_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class, 'project_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'projects_members', 'project_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'project_id');
    }

    public function checklistItems(): HasManyThrough
    {
        return $this->hasManyThrough(ProjectChecklistItem::class, ProjectTask::class, 'project_id', 'task_id');
    }

    public function phases(): HasMany
    {
        return $this->hasMany(ProjectPhase::class, 'project_id')->orderBy('sort_order');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocument::class, 'project_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ProjectMessage::class, 'project_id')->oldest();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ProjectActivity::class, 'project_id')->latest('created_at');
    }

    public function timeLogs(): HasMany
    {
        return $this->hasMany(ProjectTimeLog::class, 'project_id')->latest();
    }

    public function template(): HasOne
    {
        return $this->hasOne(ProjectTemplate::class, 'source_project_id')->latestOfMany();
    }

    public function templates(): HasMany
    {
        return $this->hasMany(ProjectTemplate::class, 'source_project_id');
    }

    public function roleOf(int $userId): ?string
    {
        return $this->members()->where('user_id', $userId)->value('role');
    }

    public function isManager(int $userId): bool
    {
        if ($this->created_by === $userId) {
            return true;
        }
        return $this->roleOf($userId) === 'manager';
    }

    public function userHasPermission(int $userId, string $permission): bool
    {
        $roleSlug = $this->roleOf($userId) ?: ($this->created_by === $userId ? 'manager' : null);
        if (!$roleSlug) {
            return false;
        }

        $role = ProjectRole::where('name', $roleSlug)->first();
        return $role ? $role->hasPermission($permission) : false;
    }

    public function userCanViewDocumentCategory(int $userId, ?string $category): bool
    {
        if (!$this->userHasPermission($userId, 'documents.view')) {
            return false;
        }

        if (empty($category)) {
            return true;
        }

        $roleSlug = $this->roleOf($userId) ?: ($this->created_by === $userId ? 'manager' : null);
        if (!$roleSlug) {
            return false;
        }

        $role = ProjectRole::where('name', $roleSlug)->first();
        return $role ? $role->canViewDocumentCategory($category) : false;
    }

    public function allowedDocumentCategoriesFor(int $userId): array
    {
        $all = ProjectDocument::getCategories();

        if (!$this->userHasPermission($userId, 'documents.view')) {
            return [];
        }

        return array_values(array_filter($all, fn($cat) => $this->userCanViewDocumentCategory($userId, $cat)));
    }

    public function canEdit(int $userId): bool
    {
        if ($this->isManager($userId)) {
            return true;
        }
        return $this->userHasPermission($userId, 'projects.edit');
    }

    public function totalChecklistCount(): int
    {
        return $this->checklistItems()->count();
    }

    public function activeChecklistCount(): int
    {
        return $this->checklistItems()
            ->where(function ($q) {
                $q->whereNull('projects_checklist_items.status_id')
                  ->orWhereDoesntHave('status', function ($sq) {
                      $sq->where('attributes->is_canceled', true)
                         ->orWhere('name', 'like', '%لغو%');
                  });
            })
            ->count();
    }

    public function completedChecklistCount(): int
    {
        return $this->checklistItems()
            ->where(function ($q) {
                $q->where('projects_checklist_items.is_done', true)
                  ->orWhereHas('status', function ($sq) {
                      $sq->where('attributes->is_completed', true)
                         ->orWhere('name', 'like', '%تکمیل%');
                  });
            })
            ->count();
    }

    public function activeTasksCount(): int
    {
        return $this->tasks()
            ->where(function ($q) {
                $q->whereNull('status_id')
                  ->orWhereDoesntHave('status', function ($sq) {
                      $sq->where('attributes->is_canceled', true)
                         ->orWhere('name', 'like', '%لغو%');
                  });
            })
            ->count();
    }

    public function completedTasksCount(): int
    {
        return $this->tasks()
            ->whereHas('status', function ($sq) {
                $sq->where('attributes->is_completed', true)
                   ->orWhere('name', 'like', '%تکمیل%');
            })
            ->count();
    }

    public function calculateProgress(): int
    {
        $totalChecklists = $this->totalChecklistCount();
        $activeChecklists = $this->activeChecklistCount();
        $doneChecklists = $this->completedChecklistCount();

        if ($totalChecklists > 0) {
            if ($activeChecklists === 0) {
                return 100;
            }
            return (int) round(($doneChecklists / $activeChecklists) * 100);
        }

        // Fallback for projects without subtasks/checklist items yet
        $activeTasks = $this->activeTasksCount();
        if ($activeTasks === 0) {
            return $this->tasks()->count() > 0 ? 100 : 0;
        }

        $doneTasks = $this->completedTasksCount();
        return (int) round(($doneTasks / $activeTasks) * 100);
    }

    public function syncStatusFromTasks(): ?ProjectStatus
    {
        if ($this->isCanceled()) {
            return $this->status;
        }

        $totalChecklists = $this->totalChecklistCount();
        $activeChecklists = $this->activeChecklistCount();
        $doneChecklists = $this->completedChecklistCount();

        $targetStatus = null;
        if ($this->isOverdue()) {
            $targetStatus = ProjectStatus::delayedFor('project');
        } elseif ($totalChecklists > 0) {
            if ($activeChecklists === 0) {
                $targetStatus = ProjectStatus::completedFor('project');
            } elseif ($doneChecklists === 0) {
                $targetStatus = ProjectStatus::queuedFor('project') ?? ProjectStatus::defaultFor('project');
            } elseif ($doneChecklists >= $activeChecklists) {
                $targetStatus = ProjectStatus::completedFor('project');
            } else {
                $targetStatus = ProjectStatus::inProgressFor('project');
            }
        } else {
            $taskTotal = $this->tasks()->count();
            $taskActiveTotal = $this->activeTasksCount();
            $taskDone = $this->completedTasksCount();

            if ($taskActiveTotal === 0) {
                if ($taskTotal > 0) {
                    $targetStatus = ProjectStatus::completedFor('project');
                } else {
                    $targetStatus = ProjectStatus::queuedFor('project') ?? ProjectStatus::defaultFor('project');
                }
            } elseif ($taskDone === 0) {
                $targetStatus = ProjectStatus::queuedFor('project') ?? ProjectStatus::defaultFor('project');
            } elseif ($taskDone >= $taskActiveTotal) {
                $targetStatus = ProjectStatus::completedFor('project');
            } else {
                $targetStatus = ProjectStatus::inProgressFor('project');
            }
        }

        if ($targetStatus && $this->status_id !== $targetStatus->id) {
            $this->update([
                'status_id' => $targetStatus->id,
            ]);
        }

        return $this->fresh('status')->status;
    }

    public function refreshProgress(): void
    {
        $this->update(['progress' => $this->calculateProgress()]);
        $this->syncStatusFromTasks();
    }

    public function daysRemaining(): ?int
    {
        if (!$this->end_date) {
            return null;
        }
        return (int) round(now()->startOfDay()->diffInDays($this->end_date->startOfDay(), false));
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
        if (!$this->end_date) {
            return false;
        }
        if ($this->isCompleted() || $this->isCanceled()) {
            return false;
        }
        return $this->end_date->isPast() && !$this->end_date->isToday();
    }

    public function isCanceled(): bool
    {
        return (bool) ($this->status?->isCanceled() || str_contains($this->status?->name ?? '', 'لغو') || str_contains(strtolower($this->status?->name ?? ''), 'cancel'));
    }

    public function isInProgress(): bool
    {
        if ($this->isCanceled() || $this->isCompleted()) {
            return false;
        }
        return (bool) ($this->status?->isInProgress() || str_contains($this->status?->name ?? '', 'انجام') || str_contains(strtolower($this->status?->name ?? ''), 'progress'));
    }

    public function isQueued(): bool
    {
        if ($this->isCanceled() || $this->isCompleted()) {
            return false;
        }
        return (bool) ($this->status?->isQueued() || str_contains($this->status?->name ?? '', 'صف') || str_contains(strtolower($this->status?->name ?? ''), 'queue'));
    }

    public function isTemplate(): bool
    {
        return (bool) $this->is_template;
    }

    public function scopeTemplates($query)
    {
        return $query->where('is_template', true);
    }

    public function scopeProjectsOnly($query)
    {
        return $query->where('is_template', false);
    }
}
