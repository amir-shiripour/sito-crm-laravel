<?php

namespace Modules\Booking\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BookingCategory extends Model
{
    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_INACTIVE = 'INACTIVE';

    protected $table = 'booking_categories';

    protected $fillable = [
        'name',
        'slug',
        'status',
        'creator_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $m) {
            if (!$m->slug) {
                $m->slug = static::makeUniqueSlug($m->name);
            }
        });

        static::updating(function (self $m) {
            if ($m->isDirty('name') && !$m->isDirty('slug')) {
                // keep slug stable by default (do not auto change)
            }
        });
    }

    protected static function makeUniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        if (!$base) {
            $base = 'cat-' . Str::lower(Str::random(6));
        }

        $slug = $base;
        $i = 1;
        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
