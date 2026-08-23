<?php

namespace Modules\Projects\App\Http\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTemplate extends Model
{
    protected $table = 'projects_templates';

    protected $fillable = [
        'title',
        'description',
        'category_id',
        'source_project_id',
        'structure',
        'created_by',
    ];

    protected $casts = [
        'structure' => 'array',
    ];

    protected $appends = [
        'phases_count',
        'tasks_count',
        'items_count',
    ];

    public function sourceProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'source_project_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getPhasesCountAttribute(): int
    {
        $phases = $this->structure['phases'] ?? [];
        return is_array($phases) ? count($phases) : 0;
    }

    public function getTasksCountAttribute(): int
    {
        $count = 0;
        $phases = $this->structure['phases'] ?? [];
        if (is_array($phases)) {
            foreach ($phases as $ph) {
                $tasks = $ph['tasks'] ?? [];
                if (is_array($tasks)) {
                    $count += count($tasks);
                }
            }
        }
        $unphased = $this->structure['unphased_tasks'] ?? [];
        if (is_array($unphased)) {
            $count += count($unphased);
        }
        return $count;
    }

    public function getItemsCountAttribute(): int
    {
        $count = 0;
        $phases = $this->structure['phases'] ?? [];
        if (is_array($phases)) {
            foreach ($phases as $ph) {
                $tasks = $ph['tasks'] ?? [];
                if (is_array($tasks)) {
                    foreach ($tasks as $t) {
                        $items = $t['items'] ?? [];
                        if (is_array($items)) {
                            $count += count($items);
                        }
                    }
                }
            }
        }
        $unphased = $this->structure['unphased_tasks'] ?? [];
        if (is_array($unphased)) {
            foreach ($unphased as $t) {
                $items = $t['items'] ?? [];
                if (is_array($items)) {
                    $count += count($items);
                }
            }
        }
        return $count;
    }
}
