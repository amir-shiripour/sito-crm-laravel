<?php

declare(strict_types=1);

namespace Modules\SmartBot\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Relations\HasMany;

final class BotAnswer extends Model
{
    protected $fillable = [
        'question_id',
        'answer_text',
        'answer_type',
        'entity_type',
        'entity_ids',
        'show_add_to_cart',
        'is_default',
        'smart_attachments',
    ];

    protected $casts = [
        'entity_ids' => 'array',
        'smart_attachments' => 'array',
        'show_add_to_cart' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(BotQuestion::class, 'question_id');
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(BotMenuItem::class, 'answer_id');
    }

    public function rootMenuItems(): HasMany
    {
        return $this->hasMany(BotMenuItem::class, 'answer_id')
            ->whereNull('parent_item_id')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->with('children');
    }

    public function activeRootMenuItems(): HasMany
    {
        return $this->hasMany(BotMenuItem::class, 'answer_id')
            ->whereNull('parent_item_id')
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->with('activeChildren');
    }
}
