<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomUserField extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_name',
        'field_name',
        'label',
        'field_type',
        'is_required',
        'show_in_register',
        'show_in_profile',
        'rules',
    ];

    public function role()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class, 'role_name', 'name');
    }

    protected $casts = [
        'meta'  => 'array', // why: دسترسی آسان به mimes/max/options
        'rules' => 'array',
        'is_required' => 'bool',
        'show_in_register' => 'bool',
        'show_in_profile' => 'bool',
    ];
}
