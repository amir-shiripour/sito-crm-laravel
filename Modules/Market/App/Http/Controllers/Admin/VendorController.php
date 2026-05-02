<?php

namespace Modules\Market\App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Modules\Market\Entities\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendor::with('user');

        // فیلتر جستجوی متنی
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('store_name', 'like', "%$search%")
                    ->orWhere('slug', 'like', "%$search%")
                    ->orWhere('national_code', 'like', "%$search%")
                    ->orWhereHas('user', function($uq) use ($search) {
                        $uq->where('name', 'like', "%$search%")
                            ->orWhere('mobile', 'like', "%$search%");
                    });
            });
        }

        // فیلتر وضعیت فعالیت
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // فیلتر وضعیت احراز هویت (بسیار مهم برای پیدا کردن ردی‌ها یا در حال بررسی‌ها)
        if ($request->filled('kyc_status')) {
            $query->where('kyc_status', $request->kyc_status);
        }

        // withQueryString برای حفظ شدن فیلترها هنگام رفتن به صفحات بعدی (Pagination)
        $vendors = $query->latest()->paginate(15)->withQueryString();

        return view('market::admin.vendors.index', compact('vendors'));
    }

    public function create()
    {
        return view('market::admin.vendors.create');
    }

    public function edit(Vendor $vendor)
    {
        $lockKey = 'vendor_edit_lock_' . $vendor->id;
        $lockedByUserId = Cache::get($lockKey);

        // بررسی اینکه آیا قفل وجود دارد و متعلق به شخص دیگری است؟
        if ($lockedByUserId && $lockedByUserId !== auth()->id()) {
            $lockedUser = User::find($lockedByUserId);
            $name = $lockedUser ? $lockedUser->name : 'یکی دیگر از همکاران';

            // در سیستم شما احتمالا سشن error توسط layout خوانده می‌شود
            return redirect()->route('user.market.vendors.index')
                ->with('error', "این درخواست در حال حاضر توسط «{$name}» در حال بررسی است. لطفاً دقایقی دیگر تلاش کنید.");
        }

        // قفل کردن رکورد برای 10 دقیقه به نام کاربر فعلی (اگر تب مرورگر را بست، بعد 10 دقیقه آزاد می‌شود)
        Cache::put($lockKey, auth()->id(), now()->addMinutes(10));

        return view('market::admin.vendors.edit', compact('vendor'));
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();
        return redirect()->route('user.market.vendors.index')
            ->with('success', 'فروشگاه با موفقیت حذف شد.');
    }
}
