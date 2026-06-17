<?php

namespace Modules\Market\App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Modules\Market\Entities\MasterProduct;
use Illuminate\Http\Request;

class MasterProductController extends Controller
{
    public function index(Request $request)
    {
        $query = MasterProduct::with(['brand', 'category']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('title', 'like', "%$search%")
                ->orWhere('crm_code', 'like', "%$search%")
                ->orWhere('barcode', 'like', "%$search%");
        }

        $products = $query->latest()->paginate(15)->withQueryString();

        return view('market::admin.master-products.index', compact('products'));
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

    public function edit(MasterProduct $master_product)
    {
        $this->authorizeVendorCatalog();
        return view('market::admin.master-products.edit', ['product' => $master_product]);
    }

    public function destroy(MasterProduct $master_product)
    {
        $this->authorizeVendorCatalog();
        $master_product->delete();
        return redirect()->route('user.market.master-products.index')
            ->with('success', 'محصول با موفقیت از کاتالوگ حذف شد.');
    }
}
