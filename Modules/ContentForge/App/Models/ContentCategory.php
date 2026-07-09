<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ContentCategory extends Model
{
    use SoftDeletes;

    protected $table = 'content_categories';

    protected $fillable = [
        'entity_id',
        'parent_id',
        'name',
        'slug',
        'description',
        'cover_image',
        'theme_key',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'canonical_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function getUrlAttribute(): string
    {
        return url('/blog/category/' . $this->slug);
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(ContentEntity::class, 'entity_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(ContentPost::class, 'category_id');
    }
}
