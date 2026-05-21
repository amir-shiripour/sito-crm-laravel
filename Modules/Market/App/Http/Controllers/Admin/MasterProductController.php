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

    public function create()
    {
        return view('market::admin.master-products.create');
    }

    public function edit(MasterProduct $master_product)
    {
        return view('market::admin.master-products.edit', ['product' => $master_product]);
    }

    public function destroy(MasterProduct $master_product)
    {
        $master_product->delete();
        return redirect()->route('user.market.master-products.index')
            ->with('success', 'محصول با موفقیت از کاتالوگ حذف شد.');
    }
}
