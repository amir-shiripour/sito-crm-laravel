<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ContentRedirect extends Model
{
    protected $table = 'content_redirects';

    protected $fillable = [
        'from_url',
        'to_url',
        'type',
        'entity_id',
    ];

    public function entity(): BelongsTo
    {
        return $this->belongsTo(ContentEntity::class, 'entity_id');
    }
}
