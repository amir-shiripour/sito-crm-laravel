<?php

namespace Modules\Booking\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class DoctorMedia extends Model
{
    protected $table = 'doctor_media';
    protected $fillable = [
        'user_id',
        'type',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'sort_order',
    ];
    protected $casts=[
        'file_size'=>'integer',
        'sort_order'=>'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
