<?php

namespace Modules\Services\App\Http\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
class ActivityLog extends Model
{

    protected $table = 'services_activity_log';
    const UPDATED_AT    = null;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'properties',
        'ip_address',
    ];

    protected $casts = ['properties' => 'array'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public static function log(string $action, Model $subject, ?string $description = null, array $props = []): self
    {
        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'properties' => $props,
            'ip_address' => request()->ip(),
            'subject_type' => get_class($subject),
            'subject_id' => $subject->getKey(),
        ]);
    }
}
