<?php

declare(strict_types=1);

namespace Modules\SmartBot\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

final class BotMenuItem extends Model
{
    protected $fillable = [
        'answer_id',
        'parent_item_id',
        'label',
        'icon',
        'sort_order',
        'response_type',
        'response_text',
        'response_entity_ids',
        'response_url',
        'is_active',
        'smart_attachments',
    ];

    protected $casts = [
        'response_entity_ids' => 'array',
        'smart_attachments' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function answer(): BelongsTo
    {
        return $this->belongsTo(BotAnswer::class, 'answer_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(BotMenuItem::class, 'parent_item_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(BotMenuItem::class, 'parent_item_id')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->with(['children']);
    }

    public function activeChildren(): HasMany
    {
        return $this->hasMany(BotMenuItem::class, 'parent_item_id')
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->with(['activeChildren']);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
    }

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_item_id');
    }
}
