<?php

namespace Modules\Properties\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PropertyAttributeValue extends Model
{
    use HasFactory;

    protected $fillable = ['property_id', 'attribute_id', 'value'];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function attribute()
    {
        return $this->belongsTo(PropertyAttribute::class);
    }
}
