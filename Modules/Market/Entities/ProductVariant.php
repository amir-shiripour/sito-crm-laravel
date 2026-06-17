<?php
namespace Modules\Market\Entities;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model {
    protected $table = 'market_product_variants';
    // 💡 اضافه کردن فیلدهای جدید
    protected $fillable = ['master_product_id', 'variant_code', 'variant_attributes', 'price', 'stock', 'is_active'];
    protected $casts = ['variant_attributes' => 'array'];

    public function masterProduct() { return $this->belongsTo(MasterProduct::class, 'master_product_id'); }
    public function vendorProducts() { return $this->hasMany(VendorProduct::class, 'product_variant_id'); }
    public function warehouseStocks() { return $this->hasMany(WarehouseStock::class, 'product_variant_id'); } // 💡 اضافه شد

    /**
     * 💡 FINAL: Accessor برای تولید نام واریانت از روی ساختار صحیح variant_attributes
     * مثال: رنگ: مشکی, حافظه داخلی: 256
     */
    public function getNameAttribute(): string
    {
        if (empty($this->variant_attributes) || !is_array($this->variant_attributes)) {
            return '';
        }

        $attributes = [];
        foreach ($this->variant_attributes as $key => $value) {
            if ($key === 'name' && $value === 'استاندارد') continue;
            $attributes[] = "{$key}: {$value}";
        }

        return implode(', ', $attributes);
    }
}
