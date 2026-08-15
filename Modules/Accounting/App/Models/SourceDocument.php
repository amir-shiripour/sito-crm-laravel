<?php

namespace Modules\Accounting\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SourceDocument extends Model
{
    protected $table = 'accounting_source_documents';

    protected $fillable = [
        'document_id',
        'sourceable_type',
        'sourceable_id',
        'module',
        'event_type',
        'snapshot',
    ];

    /**
     * Get the snapshot attributes as an array.
     */
    public function getSnapshotDataAttribute()
    {
        return $this->snapshot ? json_decode($this->snapshot, true) : [];
    }

    /**
     * Get the associated document.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the parent sourceable model (e.g., Invoice, Payment).
     */
    public function sourceable(): MorphTo
    {
        return $this->morphTo();
    }
}
