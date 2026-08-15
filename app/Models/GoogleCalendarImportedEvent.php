<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleCalendarImportedEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid',
        'summary',
        'description',
        'location',
        'start_at',
        'end_at',
        'is_all_day',
        'source_filename',
        'imported_by',
    ];

    protected $casts = [
        'start_at'   => 'datetime',
        'end_at'     => 'datetime',
        'is_all_day' => 'boolean',
    ];

    public function importedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}
