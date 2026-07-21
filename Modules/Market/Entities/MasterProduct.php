<?php
namespace Modules\Market\Entities;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MasterProduct extends Model {
    protected $table = 'market_master_products';

    protected $fillable = [
        'brand_id', 'category_id', 'crm_code', 'barcode', 'gtin', 'title', 'slug',
        'main_image', 'gallery_images', 'short_description', 'description', 'attributes', 'status',
        'single_sell', 'weight', 'length', 'width', 'height', 'shipping_class', 'enable_reviews',
        'variant_axes_permissions', 'enable_questions'
    ];

    protected $casts = [
        'attributes' => 'array',
        'gallery_images' => 'array',
        'variant_axes_permissions' => 'array'
    ];

    protected $appends = ['price_info', 'main_image_url'];

    public function brand() { return $this->belongsTo(Brand::class); }
    public function category() { return $this->belongsTo(Category::class); }

    public function displayCategories()
    {
        return $this->belongsToMany(DisplayCategory::class, 'market_product_display_category', 'master_product_id', 'display_category_id');
    }

    public function variants() { return $this->hasMany(ProductVariant::class, 'master_product_id'); }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class, 'master_product_id');
    }

    public function approvedReviews()
    {
        return $this->reviews()->where('status', 'approved');
    }

    public function questions()
    {
        return $this->hasMany(ProductQuestion::class, 'master_product_id');
    }

    public function approvedQuestions()
    {
        return $this->questions()->whereNull('parent_id')->where('status', 'approved');
    }

    public function getAverageRatingAttribute()
    {
        $avg = $this->approvedReviews()->avg('rating');
        return $avg ? round($avg, 1) : 0;
    }

    public function getApprovedReviewsCountAttribute()
    {
        return $this->approvedReviews()->count();
    }

    /**
     * 💡 FINAL: Accessor for the main image URL.
     */
    public function getMainImageUrlAttribute()
    {
        if ($this->main_image && Storage::disk('public')->exists($this->main_image)) {
            return Storage::url($this->main_image);
        }
        return null;
    }

    public function getPriceInfoAttribute()
    {
        // ... (متد بدون تغییر)
        $minPrice = null;
        $maxPrice = null;
        $originalPriceForMin = null;
        $hasStock = false;
        $totalStock = 0;
        $activeVariantsCount = 0;

        if ($this->relationLoaded('variants') || $this->variants()->exists()) {
            foreach ($this->variants as $variant) {
                $variantHasStock = false;

                foreach ($variant->vendorProducts as $vp) {
                    if ($vp->status === 'published' && $vp->stock > 0) {
                        $hasStock = true;
                        $variantHasStock = true;
                        $totalStock += $vp->stock;

                        $activePrice = $vp->discount_price > 0 ? $vp->discount_price : $vp->price;
                        $basePrice = $vp->price;

                        if ($minPrice === null || $activePrice < $minPrice) {
                            $minPrice = $activePrice;
                            $originalPriceForMin = $basePrice;
                        }
                        if ($maxPrice === null || $activePrice > $maxPrice) {
                            $maxPrice = $activePrice;
                        }
                    }
                }

                if ($variantHasStock) {
                    $activeVariantsCount++;
                }
            }
        }

        $discountPercent = 0;
        if ($minPrice !== null && $originalPriceForMin !== null && $originalPriceForMin > $minPrice) {
            $discountPercent = round((($originalPriceForMin - $minPrice) / $originalPriceForMin) * 100);
        }

        return [
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'original_price' => $originalPriceForMin,
            'discount_percent' => $discountPercent,
            'has_stock' => $hasStock,
            'total_stock' => $totalStock,
            'active_variants_count' => $activeVariantsCount,
        ];
    }
}
