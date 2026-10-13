<?php

namespace Modules\Properties\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PropertyImage extends Model
{
    use HasFactory;

    protected $fillable = ['property_id', 'path', 'sort_order'];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->path) {
            return asset('storage/' . $this->path);
        }
        return asset('assets/images/placeholder.jpg');
    }
}
