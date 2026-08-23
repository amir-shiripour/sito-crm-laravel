<?php

namespace Modules\Projects\App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectPhase extends Model
{
    protected $table = 'projects_phases';

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'color',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'phase_id')->orderBy('sort_order');
    }

    public function completedTasksCount(): int
    {
        return $this->tasks->filter(fn($t) => $t->status?->isCompleted())->count();
    }

    public function totalTasksCount(): int
    {
        return $this->tasks->count();
    }

    public function progress(): int
    {
        $total = $this->totalTasksCount();
        if ($total === 0) return 0;
        return (int) round(($this->completedTasksCount() / $total) * 100);
    }
}
