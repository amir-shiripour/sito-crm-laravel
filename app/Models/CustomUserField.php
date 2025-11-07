<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomUserField extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_name','field_name','label','field_type','is_required','rules',
    ];

    protected $casts = [
        'meta'  => 'array', // why: دسترسی آسان به mimes/max/options
        'rules' => 'array',
        'is_required' => 'bool',
    ];
}
