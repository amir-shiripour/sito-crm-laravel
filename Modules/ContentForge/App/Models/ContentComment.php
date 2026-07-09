<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use Modules\ContentForge\App\Enums\CommentStatus;

final class ContentComment extends Model
{
    use SoftDeletes;

    protected $table = 'content_comments';

    protected $fillable = [
        'post_id',
        'parent_id',
        'user_id',
        'author_name',
        'author_email',
        'body',
        'status',
        'ip_address',
    ];

    protected $casts = [
        'status' => CommentStatus::class,
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(ContentPost::class, 'post_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('created_at', 'asc');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', CommentStatus::Approved);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', CommentStatus::Pending);
    }
}
