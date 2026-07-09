<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ContentShortLink extends Model
{
    protected $table = 'content_short_links';

    protected $fillable = [
        'post_id',
        'code',
        'custom_code',
        'click_count',
        'expires_at',
    ];

    protected $casts = [
        'click_count' => 'integer',
        'expires_at'  => 'immutable_datetime',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(ContentPost::class, 'post_id');
    }
}
