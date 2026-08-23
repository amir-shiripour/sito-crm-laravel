<?php

namespace Modules\Settings\Entities;

use Illuminate\Database\Eloquent\Model;

class MenuCustomGroup extends Model
{
    protected $table = 'menu_custom_groups';

    protected $fillable = [
        'group_key',
        'title',
        'icon',
        'position',
        'scope',
        'scope_id',
        'is_active',
    ];

    protected $casts = [
        'position' => 'integer',
        'scope_id' => 'integer',
        'is_active' => 'boolean',
    ];
}
