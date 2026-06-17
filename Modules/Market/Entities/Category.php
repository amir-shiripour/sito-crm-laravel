<?php

namespace Modules\Market\Entities;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'market_categories';

    protected $fillable = [
        'parent_id', 'brand_id', 'name', 'slug', 'code_offset',
        'description', 'icon', 'meta_title', 'meta_description', 'sort_order', 'target_attributes', 'variant_fields', 'is_active',
    ];
    protected $casts = [
        'target_attributes' => 'array',
        'variant_fields' => 'array', // اضافه شد
    ];

    // رابطه با برند
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    // رابطه با دسته پدر
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // رابطه با زیردسته‌ها
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // رابطه با محصولات این دسته
    // Category.php
    public function products()
    {
        // تغییر از Product به VendorProduct
        return $this->hasMany(VendorProduct::class, 'category_id');
    }
}
