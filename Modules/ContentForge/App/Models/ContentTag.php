<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class ContentTag extends Model
{
    protected $table = 'content_tags';

    protected $fillable = [
        'entity_id',
        'name',
        'slug',
    ];

    public function getUrlAttribute(): string
    {
        return url('/blog/tag/' . $this->slug);
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(ContentEntity::class, 'entity_id');
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(ContentPost::class, 'content_post_tag', 'tag_id', 'post_id');
    }
}
