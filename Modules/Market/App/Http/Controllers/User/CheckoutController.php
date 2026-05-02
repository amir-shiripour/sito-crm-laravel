<?php

namespace Modules\Market\App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Market\Services\OrderService;
use Exception;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    protected $orderService;

    // تزریق (Inject) کردن سرویس سفارشات به کنترلر
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * پردازش و ثبت سفارش نهایی
     */
    public function store(Request $request)
    {
        // 1. اعتبارسنجی درخواست (در حالت واقعی بهتر است از FormRequest استفاده شود)
        $validated = $request->validate([
            'cart_items' => 'required|array|min:1',
            'cart_items.*.product_id' => 'required|exists:market_products,id',
            'cart_items.*.quantity' => 'required|integer|min:1',
            'shipping_address' => 'required|array',
            'shipping_method' => 'required|string',
        ]);

        try {
            // 2. واگذاری تمام منطق پیچیده دیتابیس و انبار به Service
            $shippingData = [
                'address' => $validated['shipping_address'],
                'method'  => $validated['shipping_method'],
                'cost'    => 50000, // اینجا در آینده می‌تونی به سرویس پست متصل کنی
                'discount'=> 0
            ];

            $order = $this->orderService->placeOrder(
                auth()->user(),
                $validated['cart_items'],
                $shippingData
            );

            // 3. در صورت موفقیت، ارجاع کاربر به درگاه پرداخت یا صفحه موفقیت
            return response()->json([
                'success' => true,
                'message' => 'سفارش با موفقیت ثبت شد.',
                'order_id' => $order->id,
                'redirect_url' => route('user.market.payment', $order->id) // مسیری که بعدا برای درگاه میسازیم
            ]);

        } catch (Exception $e) {
            // ثبت خطای دقیق در فایل لاگ سیستم
            Log::error('Market Checkout Error: ' . $e->getMessage());

            // نمایش پیام خطای کاربرپسند (مثلاً "موجودی کافی نیست")
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
