<?php

namespace Modules\Market\Entities;

use Illuminate\Database\Eloquent\Model;

class DisplayCategory extends Model
{
    protected $table = 'market_display_categories';

    protected $fillable = [
        'parent_id', 'name', 'slug', 'description', 'icon', 'is_active'
    ];

    // رابطه با دسته پدر
    public function parent()
    {
        return $this->belongsTo(DisplayCategory::class, 'parent_id');
    }

    // رابطه با زیردسته‌ها
    public function children()
    {
        return $this->hasMany(DisplayCategory::class, 'parent_id');
    }
}
