<?php

namespace Modules\Market\App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Modules\Market\Entities\MasterProduct;
use Modules\Market\Entities\Category;
use Modules\Market\Entities\Brand;
use Illuminate\Http\Request;

class MasterProductController extends Controller
{
    public function index(Request $request)
    {
        $query = MasterProduct::with(['brand', 'category']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                    ->orWhere('crm_code', 'like', "%$search%")
                    ->orWhere('barcode', 'like', "%$search%")
                    ->orWhere('gtin', 'like', "%$search%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        $separateCategoryEnabled = (bool) \Modules\Market\Entities\MarketSetting::getValue('system.separate_category_enabled', false);
        if ($separateCategoryEnabled && $request->filled('display_category_id')) {
            $query->whereHas('displayCategories', function($q) use ($request) {
                $q->where('display_category_id', $request->display_category_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $products = $query->latest()->paginate(15)->withQueryString();
        
        $categoriesAll = Category::all();
        $categories = $this->buildCategoryOptions($categoriesAll);

        $brands = Brand::orderBy('name')->get();
        
        $displayCategories = [];
        if ($separateCategoryEnabled) {
            $displayCategoriesAll = \Modules\Market\Entities\DisplayCategory::where('is_active', true)->get();
            $displayCategories = $this->buildDisplayCategoryOptions($displayCategoriesAll);
        }

        session(['master_products_index_url' => $request->fullUrl()]);

        return view('market::admin.master-products.index', compact(
            'products', 'categories', 'brands', 'separateCategoryEnabled', 'displayCategories'
        ));
    }

    private function buildCategoryOptions($categories, $parentId = null, $depth = 0)
    {
        $options = [];
        $filtered = $categories->where('parent_id', $parentId);

        foreach ($filtered as $cat) {
            $options[] = (object)[
                'id' => $cat->id,
                'name' => str_repeat('— ', $depth) . $cat->name,
                'brand_id' => $cat->brand_id,
            ];
            $options = array_merge($options, $this->buildCategoryOptions($categories, $cat->id, $depth + 1));
        }

        return $options;
    }

    private function buildDisplayCategoryOptions($categories, $parentId = null, $depth = 0)
    {
        $options = [];
        $filtered = $categories->where('parent_id', $parentId);

        foreach ($filtered as $cat) {
            $options[] = (object)[
                'id' => $cat->id,
                'name' => str_repeat('— ', $depth) . $cat->name,
            ];
            $options = array_merge($options, $this->buildDisplayCategoryOptions($categories, $cat->id, $depth + 1));
        }

        return $options;
    }

    protected function authorizeVendorCatalog()
    {
        $user = auth()->user();
        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return;
        }

        $vendorCanCreate = (bool) \Modules\Market\Entities\MarketSetting::getValue('vendors.vendor_can_create_catalog', false);
        if (!$vendorCanCreate) {
            abort(403, 'شما اجازه ثبت یا تغییر کاتالوگ محصولات را ندارید.');
        }
    }

    public function create()
    {
        $this->authorizeVendorCatalog();
        return view('market::admin.master-products.create');
    }

    public function import()
    {
        $this->authorizeVendorCatalog();
        return view('market::admin.master-products.import');
    }

    public function edit(MasterProduct $master_product)
    {
        $this->authorizeVendorCatalog();
        return view('market::admin.master-products.edit', ['product' => $master_product]);
    }

    public function destroy(MasterProduct $master_product)
    {
        $this->authorizeVendorCatalog();
        $master_product->delete();
        return redirect()->to(session('master_products_index_url', route('user.market.master-products.index')))
            ->with('success', 'محصول با موفقیت از کاتالوگ حذف شد.');
    }

    public function bulkAction(Request $request)
    {
        $this->authorizeVendorCatalog();

        $action = $request->input('action');
        $productIds = $request->input('product_ids', []);

        if (empty($productIds)) {
            return redirect()->back()->with('error', 'هیچ محصولی انتخاب نشده است.');
        }

        if ($action === 'delete') {
            // Check delete permission
            $canDelete = auth()->user()->hasAnyRole(['super-admin', 'admin']) || 
                (auth()->user()->can('market.master-products.delete') && \Modules\Market\Entities\MarketSetting::getValue('vendors.vendor_can_create_catalog', false));

            if (!$canDelete) {
                abort(403, 'شما اجازه حذف محصولات کاتالوگ را ندارید.');
            }

            MasterProduct::whereIn('id', $productIds)->delete();
            return redirect()->back()->with('success', 'محصولات انتخاب شده با موفقیت حذف شدند.');
        }

        if ($action === 'status') {
            $status = $request->input('status');
            if (!in_array($status, ['active', 'draft', 'archived'])) {
                return redirect()->back()->with('error', 'وضعیت نامعتبر است.');
            }

            MasterProduct::whereIn('id', $productIds)->update(['status' => $status]);
            return redirect()->back()->with('success', 'وضعیت محصولات انتخاب شده با موفقیت بروزرسانی شد.');
        }

        return redirect()->back()->with('error', 'عملیات نامعتبر است.');
    }
}
