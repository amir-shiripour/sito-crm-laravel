<?php

namespace Modules\Projects\App\Http\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTimeLog extends Model
{
    protected $table = 'projects_time_logs';

    protected $fillable = [
        'project_id',
        'task_id',
        'user_id',
        'started_at',
        'ended_at',
        'duration_minutes',
        'note',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'task_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isRunning(): bool
    {
        return is_null($this->ended_at);
    }

    public function formattedDuration(): string
    {
        $minutes = $this->duration_minutes;
        if ($this->isRunning() && $this->started_at) {
            $minutes = (int) now()->diffInMinutes($this->started_at);
        }

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($hours > 0) {
            return sprintf('%d ساعت و %d دقیقه', $hours, $mins);
        }

        return sprintf('%d دقیقه', $mins);
    }
}
