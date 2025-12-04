<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as SpatieRole;

class WidgetSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_id',
        'widget_key',
        'is_active',
    ];

    public function role()
    {
        return $this->belongsTo(SpatieRole::class, 'role_id');
    }
}
