<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

final class ContentPostRevision extends Model
{
    protected $table = 'content_post_revisions';

    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'user_id',
        'title',
        'body',
        'body_html',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'immutable_datetime',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(ContentPost::class, 'post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
