<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VersionLog extends Model
{
    protected $fillable = [
        'version_number',
        'title',
        'release_date',
        'summary',
        'changelog',
        'is_current'
    ];

    protected $casts = [
        'changelog' => 'array',
        'release_date' => 'date',
        'is_current' => 'boolean',
    ];

    /**
     * متد برای دریافت آخرین نسخه فعال سیستم
     */
    public static function current()
    {
        return self::where('is_current', true)->first() ?? self::orderBy('version_number', 'desc')->first();
    }
}
