<?php
namespace Modules\Market\Entities;
use Illuminate\Database\Eloquent\Model;

class MasterProduct extends Model {
    protected $table = 'market_master_products';
    protected $fillable = ['brand_id', 'category_id', 'crm_code', 'barcode', 'title', 'slug', 'main_image', 'gallery_images', 'description', 'attributes', 'status'];
    protected $casts = [
        'attributes' => 'array',
        'gallery_images' => 'array'
    ];

    public function brand() { return $this->belongsTo(Brand::class); }
    public function category() { return $this->belongsTo(Category::class); }

    public function variants() { return $this->hasMany(ProductVariant::class, 'master_product_id'); }
}
