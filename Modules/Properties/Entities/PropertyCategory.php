<?php

namespace Modules\Properties\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class PropertyCategory extends Model
{
    use HasFactory;

    protected $table = 'property_categories';

    protected $fillable = [
        'name',
        'color',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function properties()
    {
        return $this->hasMany(Property::class, 'category_id');
    }
}
