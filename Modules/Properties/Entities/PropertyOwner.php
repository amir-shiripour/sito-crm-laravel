<?php

namespace Modules\Properties\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class PropertyOwner extends Model
{
    use HasFactory;

    protected $table = 'property_owners';

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'created_by',
    ];

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function properties()
    {
        return $this->hasMany(Property::class, 'owner_id');
    }
}
