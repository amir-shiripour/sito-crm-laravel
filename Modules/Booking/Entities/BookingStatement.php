<?php

namespace Modules\Booking\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class BookingStatement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider_id',
        'start_date',
        'end_date',
        'first_appointment_time',
        'last_appointment_time',
        'status',
        'roles_data',
        'notes'
    ];

    protected $casts = [
        'roles_data' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_APPROVED = 'approved';
    const STATUS_COMPLETED = 'completed';

    public static function getStatuses()
    {
        return [
            self::STATUS_DRAFT => 'پیش نویس',
            self::STATUS_APPROVED => 'تایید شده',
            self::STATUS_COMPLETED => 'تکمیل شده',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}
