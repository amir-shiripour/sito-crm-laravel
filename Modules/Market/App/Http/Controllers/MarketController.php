<?php

namespace Modules\Market\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Market\Entities\MarketSetting;
use Modules\Market\Entities\Vendor;
use Modules\Market\Entities\MasterProduct;
use Modules\Market\Entities\ProductVariant;
use Modules\Market\Entities\Category;
use Modules\Market\Entities\MarketAttribute;
use Illuminate\Support\Facades\DB;

class MarketController extends Controller
{
    public function index()
    {
        $displayType = MarketSetting::getValue('system.store_display_type', 'by_vendor');
        $variantMode = MarketSetting::getValue('general.variant_display_mode', 'grouped');

        // دریافت تنظیمات نمایشی (UI/UX)
        $showCategoryOnCard = MarketSetting::getValue('ui.show_category_on_card', true);

        // Geolocation ordering city filter
        $city = null;
        if (MarketSetting::getValue('orders.enable_geolocation_ordering', false)) {
            $loc = \Modules\Market\App\Helpers\GeolocationHelper::getClientLocation();
            $city = $loc['city'] ?? null;
        }

        if ($displayType === 'by_product') {

            if ($variantMode === 'separated') {
                $items = ProductVariant::with(['masterProduct.brand', 'masterProduct.category', 'vendorProducts' => function($q) use ($city) {
                        if ($city) {
                            $q->whereHas('vendor.addresses', function($q2) use ($city) {
                                $q2->where('city', $city);
                            });
                        }
                    }])
                    ->whereHas('masterProduct', function($q) {
                        $q->where('status', 'active');
                    })
                    ->whereHas('vendorProducts', function($q) use ($city) {
                        $q->where('status', 'published')->where('stock', '>', 0);
                        if ($city) {
                            $q->whereHas('vendor.addresses', function($q2) use ($city) {
                                $q2->where('city', $city);
                            });
                        }
                    })
                    // ترفند: شمارش فروشندگان دارای موجودی برای مرتب‌سازی
                    ->withCount(['vendorProducts as has_active_stock' => function($q) use ($city) {
                        $q->where('status', 'published')->where('stock', '>', 0);
                        if ($city) {
                            $q->whereHas('vendor.addresses', function($q2) use ($city) {
                                $q2->where('city', $city);
                            });
                        }
                    }])
                    // اولویت اول: موجود دارها بالا باشند
                    ->orderByDesc('has_active_stock')
                    ->latest()
                    ->take(12)
                    ->get();
            } else {
                $items = MasterProduct::with(['variants.vendorProducts' => function($q) use ($city) {
                        if ($city) {
                            $q->whereHas('vendor.addresses', function($q2) use ($city) {
                                $q2->where('city', $city);
                            });
                        }
                    }, 'brand', 'category'])
                    ->where('status', 'active')
                    ->whereHas('variants.vendorProducts', function($q) use ($city) {
                        $q->where('status', 'published')->where('stock', '>', 0);
                        if ($city) {
                            $q->whereHas('vendor.addresses', function($q2) use ($city) {
                                $q2->where('city', $city);
                            });
                        }
                    })
                    // ترفند برای گروهی: آیا در تنوع‌ها موجودی هست؟
                    ->withCount(['variants as has_active_stock' => function($q) use ($city) {
                        $q->whereHas('vendorProducts', function ($q2) use ($city) {
                            $q2->where('status', 'published')->where('stock', '>', 0);
                            if ($city) {
                                $q2->whereHas('vendor.addresses', function($q3) use ($city) {
                                    $q3->where('city', $city);
                                });
                            }
                        });
                    }])
                    ->orderByDesc('has_active_stock')
                    ->latest()
                    ->take(12)
                    ->get();
            }

            return view('market::web.index', [
                'displayType' => $displayType,
                'variantMode' => $variantMode,
                'showCategoryOnCard' => $showCategoryOnCard,
                'items' => $items,
            ]);
        }

        $items = Vendor::where('status', 'active');
        if ($city) {
            $items->whereHas('addresses', function($q) use ($city) {
                $q->where('city', $city);
            });
        }
        $items = $items->latest()->take(12)->get();
        return view('market::web.index', [
            'displayType' => $displayType,
            'items' => $items,
        ]);
    }

    public function category(Request $request, $slug = null)
    {
        // جایگزینی دریافت ساده با دریافت درختی دسته‌بندی‌ها
        $categoriesTree = Category::whereNull('parent_id')->with('children.children')->get();
        $currentCategory = null;
        $variantMode = MarketSetting::getValue('general.variant_display_mode', 'grouped');

        // دریافت تنظیمات نمایشی (UI/UX)
        $showCategoryOnCard = MarketSetting::getValue('ui.show_category_on_card', true);

        // دریافت دیکشنری ویژگی‌ها برای ساخت فیلترهای سایدبار
        $filterAttributes = MarketAttribute::with('values')->get();

        // Geolocation ordering city filter
        $city = null;
        if (MarketSetting::getValue('orders.enable_geolocation_ordering', false)) {
            $loc = \Modules\Market\App\Helpers\GeolocationHelper::getClientLocation();
            $city = $loc['city'] ?? null;
        }

        // 💡 دریافت کمترین و بیشترین قیمت موجود در کل فروشگاه برای اسلایدر رنج قیمت
        $priceRangeQuery = DB::table('market_vendor_products')
            ->where('status', 'published')
            ->where('stock', '>', 0);
        if ($city) {
            $priceRangeQuery->whereExists(function($q) use ($city) {
                $q->select(DB::raw(1))
                    ->from('market_vendor_addresses')
                    ->whereColumn('market_vendor_addresses.vendor_id', 'market_vendor_products.vendor_id')
                    ->where('market_vendor_addresses.city', $city);
            });
        }
        $priceRange = $priceRangeQuery->selectRaw('MIN(COALESCE(discount_price, price)) as min_price, MAX(COALESCE(discount_price, price)) as max_price')
            ->first();

        $absoluteMinPrice = $priceRange && $priceRange->min_price ? floor((float)$priceRange->min_price / 5000) * 5000 : 0;
        $absoluteMaxPrice = $priceRange && $priceRange->max_price ? ceil((float)$priceRange->max_price / 5000) * 5000 : 500000000;
        if ($absoluteMinPrice == $absoluteMaxPrice) {
            $absoluteMaxPrice += 5000; // جلوگیری از تقسیم بر صفر در جاوااسکریپت در صورت برابر بودن قیمت‌ها
        }

        // آماده‌سازی متغیرهای فیلتر قیمت اعمال شده توسط کاربر
        $minPrice = $request->filled('min_price') ? (float)str_replace(',', '', $request->min_price) : null;
        $maxPrice = $request->filled('max_price') ? (float)str_replace(',', '', $request->max_price) : null;

        if ($variantMode === 'separated') {
            $query = ProductVariant::with(['masterProduct.brand', 'masterProduct.category', 'vendorProducts' => function($q) use ($city) {
                    if ($city) {
                        $q->whereHas('vendor.addresses', function($q2) use ($city) {
                            $q2->where('city', $city);
                        });
                    }
                }])
                ->whereHas('masterProduct', function($q) {
                    $q->where('status', 'active');
                });

            // برای رفع ارور order، فیلد محاسبه شده را به select اضافه می‌کنیم
            $query->select('market_product_variants.*')
                ->withCount(['vendorProducts as has_active_stock' => function($q) use ($city) {
                    $q->where('status', 'published')->where('stock', '>', 0);
                    if ($city) {
                        $q->whereHas('vendor.addresses', function($q2) use ($city) {
                            $q2->where('city', $city);
                        });
                    }
                }]);

            if ($slug) {
                $currentCategory = Category::where('slug', $slug)->firstOrFail();
                $query->whereHas('masterProduct', function($q) use ($currentCategory) {
                    $q->where('category_id', $currentCategory->id);
                });
            }

            if ($request->has('categories') && is_array($request->categories)) {
                $query->whereHas('masterProduct', function($q) use ($request) {
                    $q->whereIn('category_id', $request->categories);
                });
            }

            if ($request->filled('q')) {
                $query->whereHas('masterProduct', function($q) use ($request) {
                    $q->where('title', 'like', '%' . $request->q . '%');
                });
            }

            if ($request->boolean('in_stock')) {
                $query->whereHas('vendorProducts', function ($q) use ($city) {
                    $q->where('status', 'published')->where('stock', '>', 0);
                    if ($city) {
                        $q->whereHas('vendor.addresses', function($q2) use ($city) {
                            $q2->where('city', $city);
                        });
                    }
                });
            }

            // اعمال فیلتر قیمت در حالت مجزا
            if ($minPrice !== null || $maxPrice !== null) {
                $query->whereHas('vendorProducts', function ($q) use ($minPrice, $maxPrice, $city) {
                    $q->where('status', 'published')->where('stock', '>', 0);
                    if ($city) {
                        $q->whereHas('vendor.addresses', function($q2) use ($city) {
                            $q2->where('city', $city);
                        });
                    }

                    if ($minPrice !== null) {
                        $q->where(function($q2) use ($minPrice) {
                            $q2->whereRaw('COALESCE(discount_price, price) >= ?', [$minPrice]);
                        });
                    }
                    if ($maxPrice !== null) {
                        $q->where(function($q2) use ($maxPrice) {
                            $q2->whereRaw('COALESCE(discount_price, price) <= ?', [$maxPrice]);
                        });
                    }
                });
            }

            // اعمال فیلتر بر اساس ویژگی‌های داینامیک انتخاب شده
            if ($request->has('attrs') && is_array($request->attrs)) {
                foreach ($request->attrs as $attrId => $values) {
                    $attr = MarketAttribute::find($attrId);
                    if ($attr && is_array($values)) {
                        $attrName = $attr->name;
                        $query->where(function($q) use ($attrName, $values) {
                            foreach($values as $val) {
                                $q->orWhereJsonContains('variant_attributes->' . $attrName, $val);
                            }
                        });
                    }
                }
            }

            // سورتینگ مجزا
            $sort = $request->sort ?? 'newest';

            if ($sort === 'price_asc' || $sort === 'price_desc') {
                $direction = $sort === 'price_asc' ? 'asc' : 'desc';

                $query->selectSub(function ($query) use ($city) {
                    $query->selectRaw('MIN(COALESCE(discount_price, price))')
                        ->from('market_vendor_products')
                        ->whereColumn('product_variant_id', 'market_product_variants.id')
                        ->where('status', 'published')
                        ->where('stock', '>', 0);
                    if ($city) {
                        $query->whereHas('vendor.addresses', function($q2) use ($city) {
                            $q2->where('city', $city);
                        });
                    }
                }, 'calculated_min_price')
                    ->orderBy('has_active_stock', 'desc')
                    ->orderByRaw('calculated_min_price IS NULL')
                    ->orderBy('calculated_min_price', $direction);

            } elseif ($sort === 'viewed') {
                $query->orderByDesc('has_active_stock')->latest();
            } elseif ($sort === 'bestselling') {
                $query->orderByDesc('has_active_stock')->latest();
            } else {
                $query->orderByDesc('has_active_stock')->latest();
            }

        } else {
            $query = MasterProduct::with(['variants.vendorProducts' => function($q) use ($city) {
                    if ($city) {
                        $q->whereHas('vendor.addresses', function($q2) use ($city) {
                            $q2->where('city', $city);
                        });
                    }
                }, 'brand', 'category'])
                ->where('status', 'active');

            // اضافه کردن select اصلی برای رفع باگ OrderBy
            $query->select('market_master_products.*')
                ->withCount(['variants as has_active_stock' => function($q) use ($city) {
                    $q->whereHas('vendorProducts', function ($q2) use ($city) {
                        $q2->where('status', 'published')->where('stock', '>', 0);
                        if ($city) {
                            $q2->whereHas('vendor.addresses', function($q3) use ($city) {
                                $q3->where('city', $city);
                            });
                        }
                    });
                }]);

            if ($slug) {
                $currentCategory = Category::where('slug', $slug)->firstOrFail();
                $query->where('category_id', $currentCategory->id);
            }

            if ($request->has('categories') && is_array($request->categories)) {
                $query->whereIn('category_id', $request->categories);
            }

            if ($request->filled('q')) {
                $query->where('title', 'like', '%' . $request->q . '%');
            }

            if ($request->boolean('in_stock')) {
                $query->whereHas('variants.vendorProducts', function ($q) use ($city) {
                    $q->where('status', 'published')->where('stock', '>', 0);
                    if ($city) {
                        $q->whereHas('vendor.addresses', function($q2) use ($city) {
                            $q2->where('city', $city);
                        });
                    }
                });
            }

            // اعمال فیلتر قیمت در حالت گروهی
            if ($minPrice !== null || $maxPrice !== null) {
                $query->whereHas('variants.vendorProducts', function ($q) use ($minPrice, $maxPrice, $city) {
                    $q->where('status', 'published')->where('stock', '>', 0);
                    if ($city) {
                        $q->whereHas('vendor.addresses', function($q2) use ($city) {
                            $q2->where('city', $city);
                        });
                    }

                    if ($minPrice !== null) {
                        $q->where(function($q2) use ($minPrice) {
                            $q2->whereRaw('COALESCE(discount_price, price) >= ?', [$minPrice]);
                        });
                    }
                    if ($maxPrice !== null) {
                        $q->where(function($q2) use ($maxPrice) {
                            $q2->whereRaw('COALESCE(discount_price, price) <= ?', [$maxPrice]);
                        });
                    }
                });
            }

            // اعمال فیلتر بر اساس ویژگی‌های داینامیک در حالت گروهی
            if ($request->has('attrs') && is_array($request->attrs)) {
                foreach ($request->attrs as $attrId => $values) {
                    $attr = MarketAttribute::find($attrId);
                    if ($attr && is_array($values)) {
                        $attrName = $attr->name;
                        $query->whereHas('variants', function($q) use ($attrName, $values) {
                            $q->where(function($q2) use ($attrName, $values) {
                                foreach($values as $val) {
                                    $q2->orWhereJsonContains('variant_attributes->' . $attrName, $val);
                                }
                            });
                        });
                    }
                }
            }

            // سورتینگ گروهی
            $sort = $request->sort ?? 'newest';

            if ($sort === 'price_asc' || $sort === 'price_desc') {
                $direction = $sort === 'price_asc' ? 'asc' : 'desc';

                $query->selectSub(function ($query) use ($city) {
                    $query->selectRaw('MIN(COALESCE(vp.discount_price, vp.price))')
                        ->from('market_vendor_products as vp')
                        ->join('market_product_variants as pv', 'vp.product_variant_id', '=', 'pv.id')
                        ->whereColumn('pv.master_product_id', 'market_master_products.id')
                        ->where('vp.status', 'published')
                        ->where('vp.stock', '>', 0);
                    if ($city) {
                        $query->join('market_vendors as v', 'vp.vendor_id', '=', 'v.id')
                            ->join('market_vendor_addresses as va', 'v.id', '=', 'va.vendor_id')
                            ->where('va.city', $city);
                    }
                }, 'calculated_min_price')
                    ->orderBy('has_active_stock', 'desc')
                    ->orderByRaw('calculated_min_price IS NULL')
                    ->orderBy('calculated_min_price', $direction);

            } elseif ($sort === 'viewed' || $sort === 'bestselling') {
                $query->orderByDesc('has_active_stock')->latest();
            } else {
                $query->orderByDesc('has_active_stock')->latest();
            }
        }

        $items = $query->paginate(15);

        return view('market::web.category', [
            'items' => $items,
            'variantMode' => $variantMode,
            'showCategoryOnCard' => $showCategoryOnCard,
            'categoriesTree' => $categoriesTree,
            'filterAttributes' => $filterAttributes,
            'currentCategory' => $currentCategory,
            'absoluteMinPrice' => $absoluteMinPrice, // 💡 متغیر جدید برای ویو
            'absoluteMaxPrice' => $absoluteMaxPrice  // 💡 متغیر جدید برای ویو
        ]);
    }

    /**
     * نمایش صفحه جزئیات محصول
     */
    public function show($slug)
    {
        // Geolocation ordering city filter
        $city = null;
        if (MarketSetting::getValue('orders.enable_geolocation_ordering', false)) {
            $loc = \Modules\Market\App\Helpers\GeolocationHelper::getClientLocation();
            $city = $loc['city'] ?? null;
        }

        $product = MasterProduct::with([
            'brand',
            'category',
            'variants.vendorProducts' => function($q) use ($city) {
                if ($city) {
                    $q->whereHas('vendor.addresses', function($q2) use ($city) {
                        $q2->where('city', $city);
                    });
                }
            },
            'variants.vendorProducts.vendor'
        ])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $relatedProducts = MasterProduct::with(['variants.vendorProducts' => function($q) use ($city) {
                if ($city) {
                    $q->whereHas('vendor.addresses', function($q2) use ($city) {
                        $q2->where('city', $city);
                    });
                }
            }, 'brand'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->inRandomOrder()
            ->take(4)
            ->get();

        $variantMode = MarketSetting::getValue('general.variant_display_mode', 'grouped');
        $showVendorInProductPage = MarketSetting::getValue('ui.show_vendor_on_product_page', true);
        $showStockWarning = MarketSetting::getValue('ui.show_stock_warning', true);

        $attributeDictionary = MarketAttribute::with('values')->get();

        return view('market::web.product.show', compact('product', 'relatedProducts', 'variantMode', 'showVendorInProductPage', 'showStockWarning', 'attributeDictionary'));
    }
}
