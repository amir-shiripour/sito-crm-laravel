<?php

namespace Modules\Projects\App\Http\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMessage extends Model
{
    protected $table = 'projects_messages';

    protected $fillable = [
        'project_id',
        'parent_id',
        'user_id',
        'body',
        'attachments',
        'is_pinned',
        'pinned_at',
        'pinned_by',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_pinned' => 'boolean',
        'pinned_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProjectMessage::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(ProjectMessage::class, 'parent_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pinnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pinned_by');
    }

    public function isPinned(): bool
    {
        return (bool) $this->is_pinned;
    }
}
