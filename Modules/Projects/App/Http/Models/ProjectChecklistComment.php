<?php

namespace Modules\Projects\App\Http\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectChecklistComment extends Model
{
    protected $table = 'projects_checklist_comments';

    protected $fillable = [
        'checklist_item_id',
        'user_id',
        'body',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(ProjectChecklistItem::class, 'checklist_item_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
