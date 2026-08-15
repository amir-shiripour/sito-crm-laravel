<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class HolidayEvent extends Model
{
    use HasFactory;

    protected $table = 'holiday_events';

    protected $fillable = [
        'jalali_year',
        'jalali_month',
        'jalali_day',
        'jalali_date',
        'gregorian_date',
        'title',
        'is_holiday',
        'description',
    ];

    protected $casts = [
        'jalali_year'    => 'integer',
        'jalali_month'   => 'integer',
        'jalali_day'     => 'integer',
        'is_holiday'     => 'boolean',
        'gregorian_date' => 'date',
    ];
}
