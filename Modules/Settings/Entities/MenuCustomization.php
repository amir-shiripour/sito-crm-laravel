<?php

namespace Modules\Settings\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class MenuCustomization extends Model
{
    protected $table = 'menu_customizations';

    protected $fillable = [
        'scope',
        'scope_id',
        'menu_key',
        'type',
        'overrides',
        'created_by',
    ];

    protected $casts = [
        'overrides' => 'array',
        'scope_id' => 'integer',
        'created_by' => 'integer',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
