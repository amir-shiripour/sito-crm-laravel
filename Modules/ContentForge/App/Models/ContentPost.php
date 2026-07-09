<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\User;
use Modules\ContentForge\App\Enums\PostStatus;
use Modules\ContentForge\App\Enums\PostType;
use Modules\ContentForge\App\Enums\PostVisibility;

final class ContentPost extends Model
{
    use SoftDeletes;

    protected $table = 'content_posts';

    protected $fillable = [
        'entity_id',
        'category_id',
        'author_id',
        'type',
        'title',
        'slug',
        'excerpt',
        'body',
        'body_html',
        'cover_image',
        'og_image',
        'gallery',
        'attachments',
        'theme_key',
        'status',
        'visibility',
        'password',
        'short_code',
        'view_count',
        'reading_time',
        'featured',
        'allow_comments',
        'comment_count',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'canonical_url',
        'schema_markup',
        'published_at',
        'scheduled_at',
        'sort_order',
    ];

    protected $casts = [
        'gallery'        => 'array',
        'attachments'    => 'array',
        'schema_markup'  => 'array',
        'status'         => PostStatus::class,
        'visibility'     => PostVisibility::class,
        'type'           => PostType::class,
        'published_at'   => 'immutable_datetime',
        'scheduled_at'   => 'immutable_datetime',
        'featured'       => 'boolean',
        'allow_comments' => 'boolean',
        'view_count'     => 'integer',
        'reading_time'   => 'integer',
        'comment_count'  => 'integer',
        'sort_order'     => 'integer',
    ];

    public function getUrlAttribute(): string
    {
        return url('/' . $this->slug);
    }

    // Relationships
    public function entity(): BelongsTo
    {
        return $this->belongsTo(ContentEntity::class, 'entity_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ContentCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ContentTag::class, 'content_post_tag', 'post_id', 'tag_id');
    }

    public function shortLink(): HasOne
    {
        return $this->hasOne(ContentShortLink::class, 'post_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ContentComment::class, 'post_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ContentPostRevision::class, 'post_id');
    }

    // Scopes
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PostStatus::Published)
            ->where(function ($q) {
                $q->whereNull('published_at')
                  ->orWhere('published_at', '<=', now());
            });
    }

    public function scopePages(Builder $query): Builder
    {
        return $query->where('type', PostType::Page);
    }

    public function scopePosts(Builder $query): Builder
    {
        return $query->where('type', PostType::Post);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }
}
