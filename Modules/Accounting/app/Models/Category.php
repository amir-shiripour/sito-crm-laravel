<?php

namespace Modules\Accounting\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'accounting_categories';

    protected $fillable = [
        'title',
        'type',
        'status',
        'is_system', // Added is_system
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_system' => 'boolean', // Added is_system to casts
        'type' => 'string',
    ];
}
