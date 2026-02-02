<?php

namespace Modules\Booking\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingForm extends Model
{
    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_INACTIVE = 'INACTIVE';

    public const TYPE_CUSTOM = 'CUSTOM';
    public const TYPE_TOOTH_NUMBER = 'TOOTH_NUMBER';

    protected $table = 'booking_forms';

    protected $fillable = [
        'name',
        'form_type',
        'status',
        'creator_id',
        'schema_json',
    ];

    protected $casts = [
        'schema_json' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
