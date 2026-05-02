<?php

namespace Modules\Market\App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Modules\Market\Entities\VendorProduct;
use Illuminate\Http\Request;

class VendorProductController extends Controller
{
    public function index()
    {
        $vendor = auth()->user()->marketVendor;

        // 💡 اصلاح کوئری: دریافت محصولات فروشنده به همراه اطلاعات کاتالوگ مرجع و تنوع
        $products = VendorProduct::where('vendor_id', $vendor->id)
            ->with(['variant.masterProduct.category', 'variant.masterProduct.brand'])
            ->latest()
            ->paginate(15);

        return view('market::user.products.index', compact('products'));
    }

    public function create()
    {
        return view('market::user.products.create');
    }

    public function edit(VendorProduct $product)
    {
        // بررسی امنیتی
        if ($product->vendor_id !== auth()->user()->marketVendor?->id) {
            abort(403, 'شما اجازه ویرایش این محصول را ندارید.');
        }

        return view('market::user.products.edit', compact('product'));
    }

    public function destroy(VendorProduct $product)
    {
        // بررسی امنیتی
        if ($product->vendor_id !== auth()->user()->marketVendor?->id) {
            abort(403, 'شما اجازه حذف این محصول را ندارید.');
        }

        $product->delete();

        return redirect()->route('user.market.vendor.products.index')
            ->with('success', 'تنوع کالای شما با موفقیت از فروشگاه حذف شد.');
    }
}
