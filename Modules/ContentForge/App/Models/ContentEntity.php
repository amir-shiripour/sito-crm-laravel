<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ContentEntity extends Model
{
    protected $table = 'content_entities';

    protected $fillable = [
        'name',
        'slug',
        'module_source',
        'entity_reference_id',
        'theme_key',
        'settings',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'settings'   => 'array',
        'is_default' => 'boolean',
        'is_active'  => 'boolean',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(ContentPost::class, 'entity_id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(ContentCategory::class, 'entity_id');
    }

    public function tags(): HasMany
    {
        return $this->hasMany(ContentTag::class, 'entity_id');
    }
}
