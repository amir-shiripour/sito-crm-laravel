<?php

namespace Modules\Clients\App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Modules\Market\App\Models\Order as MarketOrder; // 💡 مسیر صحیح جایگزین شد

class ClientPaymentController extends Controller
{
    public function index()
    {
        $client = auth('client')->user();
        $allPayments = collect();

        // 1. Fetch Booking Payments
        if (class_exists(\Modules\Booking\Entities\BookingPayment::class) && Schema::hasTable('booking_payments')) {
            $bookingPayments = \Modules\Booking\Entities\BookingPayment::whereHas('appointment', function($q) use ($client) {
                $q->where('client_id', $client->id);
            })->with('appointment.service')->get()->map(function($payment) {
                return (object)[
                    'id' => $payment->id,
                    'ref_id' => $payment->id,
                    'type' => 'booking',
                    'type_label' => 'نوبت‌دهی (رزرو)',
                    'title' => 'پرداخت برای رزرو ' . optional(optional($payment->appointment)->service)->name,
                    'amount' => $payment->amount,
                    'status' => $payment->status,
                    'date' => $payment->created_at,
                    'payment_record' => $payment
                ];
            });
            $allPayments = $allPayments->merge($bookingPayments);
        }

        // 2. Fetch Market Orders (as invoices/payments)
        if (class_exists(MarketOrder::class) && Schema::hasTable('market_orders')) {
            $marketOrders = MarketOrder::where('client_id', $client->id)->get()->map(function($order) {
                $statusMap = [
                    'pending' => 'PENDING',
                    'paid' => 'PAID',
                    'failed' => 'FAILED',
                    'refunded' => 'REFUNDED',
                    'canceled' => 'CANCELED',
                    'unpaid'   => 'PENDING',
                ];
                $normalizedStatus = $statusMap[strtolower($order->payment_status)] ?? strtoupper($order->payment_status);

                return (object)[
                    'id' => $order->id,
                    'ref_id' => $order->id,
                    'type' => 'market',
                    'type_label' => 'سفارش فروشگاه',
                    'title' => 'سفارش خرید #' . $order->id,
                    'amount' => $order->grand_total,
                    'status' => $normalizedStatus,
                    'date' => $order->created_at,
                    'payment_record' => $order
                ];
            });
            $allPayments = $allPayments->merge($marketOrders);
        }

        // Sort by date descending
        $allPayments = $allPayments->sortByDesc('date')->values();

        // Booking currency settings
        $bookingCurrencyUnit = 'IRR';
        $bookingCurrencyLabel = 'ریال';
        if (class_exists(\Modules\Booking\Entities\BookingSetting::class) && Schema::hasTable('booking_settings')) {
            try {
                $bs = \Modules\Booking\Entities\BookingSetting::current();
                $bookingCurrencyUnit = $bs->currency_unit ?? 'IRR';
                $bookingCurrencyLabel = $bookingCurrencyUnit === 'IRT' ? 'تومان' : 'ریال';
            } catch (\Exception $e) {}
        }

        return view('clients::portal.payments.index', compact('allPayments', 'bookingCurrencyUnit', 'bookingCurrencyLabel'));
    }

    public function show($type, $id)
    {
        $client = auth('client')->user();

        if ($type === 'booking') {
            if (!class_exists(\Modules\Booking\Entities\BookingPayment::class) || !Schema::hasTable('booking_payments')) abort(404);

            $payment = \Modules\Booking\Entities\BookingPayment::whereHas('appointment', function($q) use ($client) {
                $q->where('client_id', $client->id);
            })->with(['appointment.service', 'appointment.provider'])->findOrFail($id);

            // Booking currency settings
            $bookingCurrencyUnit = $payment->currency_unit ?? 'IRR';
            $bookingCurrencyLabel = $bookingCurrencyUnit === 'IRT' ? 'تومان' : 'ریال';
            if (!$payment->currency_unit && class_exists(\Modules\Booking\Entities\BookingSetting::class) && Schema::hasTable('booking_settings')) {
                try {
                    $bs = \Modules\Booking\Entities\BookingSetting::current();
                    $bookingCurrencyUnit = $bs->currency_unit ?? 'IRR';
                    $bookingCurrencyLabel = $bookingCurrencyUnit === 'IRT' ? 'تومان' : 'ریال';
                } catch (\Exception $e) {}
            }

            $settingsMap = \Illuminate\Support\Facades\Schema::hasTable('settings')
                ? \Modules\Settings\Entities\Setting::query()->pluck('value', 'key')->toArray()
                : [];

            $activeRaw = $settingsMap['active_payment_methods'] ?? '[]';
            $activeMethods = is_string($activeRaw) ? json_decode($activeRaw, true) : (array) $activeRaw;
            if (empty($activeMethods) || !is_array($activeMethods)) {
                $activeMethods = ['online', 'pos', 'transfer', 'cod', 'installment'];
            }

            $allMethodLabels = [
                'online' => 'پرداخت آنلاین',
                'transfer' => 'انتقال بانکی (کارت به کارت / شبا)',
                'pos' => 'دستگاه کارتخوان (POS)',
                'installment' => 'اقساطی / چک',
                'cod' => 'پرداخت در محل / نقدی',
            ];

            $onlineGateways = [];
            if (($settingsMap['zarinpal_status'] ?? '') === 'active') {
                $onlineGateways[] = ['id' => 'zarinpal', 'label' => 'درگاه زرین‌پال'];
            }
            if (($settingsMap['zibal_status'] ?? '') === 'active') {
                $onlineGateways[] = ['id' => 'zibal', 'label' => 'درگاه زیبال'];
            }
            if (($settingsMap['behpardakht_status'] ?? '') === 'active') {
                $onlineGateways[] = ['id' => 'behpardakht', 'label' => 'درگاه بهپرداخت ملت'];
            }

            $rawAccounts = is_string($settingsMap['bank_transfer_accounts'] ?? null) 
                ? (json_decode($settingsMap['bank_transfer_accounts'], true) ?: []) 
                : (is_array($settingsMap['bank_transfer_accounts'] ?? null) ? $settingsMap['bank_transfer_accounts'] : []);

            $bankAccounts = array_map(function($acc, $index) {
                return [
                    'id' => $acc['id'] ?? ($acc['bank_name'] ?? ('acc_' . $index)),
                    'bank_name' => $acc['bank_name'] ?? 'بانک',
                    'owner_name' => $acc['owner_name'] ?? ($acc['owner'] ?? ''),
                    'card_number' => !empty($acc['card_number']) ? preg_replace('/[^0-9]/', '', $acc['card_number']) : '',
                    'account_number' => $acc['account_number'] ?? '',
                    'iban' => $acc['iban'] ?? '',
                ];
            }, $rawAccounts, array_keys($rawAccounts));

            $rawPos = is_string($settingsMap['pos_devices'] ?? null) ? (json_decode($settingsMap['pos_devices'], true) ?: []) : (array)($settingsMap['pos_devices'] ?? []);
            $posDevices = array_values(array_map(fn($d) => [
                'id' => $d['id'] ?? ($d['name'] ?? ''),
                'label' => ($d['name'] ?? 'کارتخوان') . (!empty($d['account_number']) ? ' (حساب: ' . $d['account_number'] . ')' : '')
            ], $rawPos));

            $rawInstallments = is_string($settingsMap['installment_types'] ?? null)
                ? (json_decode($settingsMap['installment_types'], true) ?: [])
                : (array)($settingsMap['installment_types'] ?? []);

            $installmentTypes = array_values(array_map(fn($i) => [
                'id' => $i['id'] ?? ($i['title'] ?? ''),
                'label' => ($i['title'] ?? 'طرح اقساطی') . (!empty($i['default_tier_config']['max_months']) ? ' (' . $i['default_tier_config']['max_months'] . ' ماهه)' : '')
            ], $rawInstallments));

            // بررسی دوطرفه: ۱. فعال بودن در چک‌باکس‌های "روش‌های پرداخت فعال سیستم" ۲. فعال بودن وضعیت اختصاصی آن روش
            $availablePaymentMethods = [];

            // ۱. درگاه پرداخت آنلاین
            if (in_array('online', $activeMethods) && !empty($onlineGateways)) {
                $availablePaymentMethods['online'] = $allMethodLabels['online'];
            }

            // ۲. انتقال بانکی (کارت به کارت / شبا)
            if (in_array('transfer', $activeMethods) && ($settingsMap['bank_transfer_status'] ?? '') === 'active') {
                $availablePaymentMethods['transfer'] = $allMethodLabels['transfer'];
            }

            // ۳. دستگاه کارتخوان (POS)
            if (in_array('pos', $activeMethods) && ($settingsMap['pos_status'] ?? '') === 'active') {
                $availablePaymentMethods['pos'] = $allMethodLabels['pos'];
            }

            // ۴. پرداخت اقساطی / چک
            if (in_array('installment', $activeMethods) && ($settingsMap['installment_status'] ?? '') === 'active') {
                $availablePaymentMethods['installment'] = $allMethodLabels['installment'];
            }

            // ۵. پرداخت در محل (نقد)
            if (in_array('cod', $activeMethods) && ($settingsMap['cod_status'] ?? '') === 'active') {
                $availablePaymentMethods['cod'] = $allMethodLabels['cod'];
            }

            $paymentSubItems = [
                'online' => $onlineGateways,
                'transfer' => array_values(array_map(fn($a) => [
                    'id' => $a['id'],
                    'label' => ($a['bank_name'] ?: 'بانک') . ($a['owner_name'] ? ' - ' . $a['owner_name'] : '') . ($a['card_number'] ? ' (' . $a['card_number'] . ')' : '')
                ], $bankAccounts)),
                'pos' => $posDevices,
                'installment' => $installmentTypes,
                'cod' => []
            ];

            return view('clients::portal.payments.show_booking', compact(
                'payment', 
                'bookingCurrencyUnit', 
                'bookingCurrencyLabel',
                'availablePaymentMethods',
                'paymentSubItems',
                'bankAccounts'
            ));
        }

        if ($type === 'market') {
            if (!class_exists(MarketOrder::class) || !Schema::hasTable('market_orders')) abort(404);

            $order = MarketOrder::where('client_id', $client->id)
                ->with(['items'])
                ->findOrFail($id);

            return view('clients::portal.payments.show_market', compact('order'));
        }

        abort(404);
    }

    public function marketOrdersIndex()
    {
        $client = auth('client')->user();
        if (!class_exists(MarketOrder::class) || !Schema::hasTable('market_orders')) abort(404);

        $orders = MarketOrder::where('client_id', $client->id)->latest()->paginate(15);
        return view('clients::portal.orders.index', compact('orders'));
    }

    public function marketOrderShow($id)
    {
        $client = auth('client')->user();
        if (!class_exists(MarketOrder::class) || !Schema::hasTable('market_orders')) abort(404);

        $order = MarketOrder::where('client_id', $client->id)
            ->with(['items.vendorProduct.variant', 'items.vendor'])
            ->findOrFail($id);

        return view('clients::portal.orders.show', compact('order'));
    }

    public function processPayment(Request $request, $id)
    {
        $client = auth('client')->user();
        
        if (!class_exists(\Modules\Booking\Entities\BookingPayment::class) || !Schema::hasTable('booking_payments')) {
            abort(404);
        }

        $payment = \Modules\Booking\Entities\BookingPayment::whereHas('appointment', function($q) use ($client) {
            $q->where('client_id', $client->id);
        })->findOrFail($id);

        if ($payment->status !== \Modules\Booking\Entities\BookingPayment::STATUS_PENDING) {
            return back()->with('error', 'این پرداخت قابل پردازش نیست.');
        }

        $method = $request->input('payment_method');

        $request->validate([
            'payment_method' => 'required|string',
            'sub_item' => 'nullable|string',
            'tracking_code' => 'nullable|string|max:255',
            'payment_date' => 'nullable|string|max:20',
            'payer_name' => 'nullable|string|max:255',
            'payer_mobile' => 'nullable|string|max:50',
            'receipt_file' => ($method !== 'online') 
                ? 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:10240' 
                : 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
        ], [
            'receipt_file.required' => 'لطفاً تصویر یا فایل رسید پرداخت را آپلود نمایید.',
        ]);

        $settingsMap = \Illuminate\Support\Facades\Schema::hasTable('settings')
            ? \Modules\Settings\Entities\Setting::query()->pluck('value', 'key')->toArray()
            : [];

        $activeRaw = $settingsMap['active_payment_methods'] ?? '[]';
        $activeMethods = is_string($activeRaw) ? json_decode($activeRaw, true) : (array) $activeRaw;
        if (empty($activeMethods) || !is_array($activeMethods)) {
            $activeMethods = ['online', 'pos', 'transfer', 'cod', 'installment'];
        }

        $isMethodValid = false;
        if ($method === 'online') {
            $hasActiveGateway = ($settingsMap['zarinpal_status'] ?? '') === 'active'
                || ($settingsMap['zibal_status'] ?? '') === 'active'
                || ($settingsMap['behpardakht_status'] ?? '') === 'active';
            $isMethodValid = in_array('online', $activeMethods) && $hasActiveGateway;
        } elseif ($method === 'pos') {
            $isMethodValid = in_array('pos', $activeMethods) && ($settingsMap['pos_status'] ?? '') === 'active';
        } elseif ($method === 'transfer') {
            $isMethodValid = in_array('transfer', $activeMethods) && ($settingsMap['bank_transfer_status'] ?? '') === 'active';
        } elseif ($method === 'cod') {
            $isMethodValid = in_array('cod', $activeMethods) && ($settingsMap['cod_status'] ?? '') === 'active';
        } elseif ($method === 'installment') {
            $isMethodValid = in_array('installment', $activeMethods) && ($settingsMap['installment_status'] ?? '') === 'active';
        }

        if (!$isMethodValid) {
            return back()->with('error', 'روش پرداخت انتخاب شده غیرفعال یا نامعتبر است.');
        }

        if ($method === 'online') {
            try {
                $paymentService = app(\Modules\Booking\Services\PaymentService::class);
                $gateway = $request->input('sub_item'); // e.g. zarinpal, zibal
                $result = $paymentService->startGateway($payment, $gateway);
                
                $url = $result['payment_url'] ?? $result['url'] ?? null;
                if (!empty($url)) {
                    return redirect()->away($url);
                }
                return back()->with('error', 'خطا در اتصال به درگاه پرداخت.');
            } catch (\Exception $e) {
                return back()->with('error', 'خطای سیستمی: ' . $e->getMessage());
            }
        } else {
            // Manual methods (transfer, pos, cod, etc)
            $subItemId = $request->input('sub_item');
            $subItemLabel = $subItemId;
            $tracking = $request->input('tracking_code');
            $date = $request->input('payment_date');
            $payerName = $request->input('payer_name');
            $payerMobile = $request->input('payer_mobile');

            if ($subItemId && \Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $settingsMap = \Modules\Settings\Entities\Setting::query()->pluck('value', 'key')->toArray();
                $rawAccounts = is_string($settingsMap['bank_transfer_accounts'] ?? null) 
                    ? (json_decode($settingsMap['bank_transfer_accounts'], true) ?: []) 
                    : (array) ($settingsMap['bank_transfer_accounts'] ?? []);
                
                foreach ($rawAccounts as $acc) {
                    $accId = $acc['id'] ?? ($acc['bank_name'] ?? '');
                    if ($accId == $subItemId) {
                        $bankName = $acc['bank_name'] ?? 'بانک';
                        $ownerName = $acc['owner_name'] ?? ($acc['owner'] ?? '');
                        $cardNumber = !empty($acc['card_number']) ? preg_replace('/[^0-9]/', '', $acc['card_number']) : '';
                        $subItemLabel = $bankName . ($ownerName ? ' - ' . $ownerName : '') . ($cardNumber ? ' (' . $cardNumber . ')' : '');
                        break;
                    }
                }
            }

            $receiptPath = null;
            if ($request->hasFile('receipt_file')) {
                try {
                    $optimizer = app(\App\Services\ImageOptimizerService::class);
                    $receiptPath = $optimizer->uploadAndOptimize(
                        file: $request->file('receipt_file'),
                        directory: 'payment-receipts',
                        disk: 'public'
                    );
                } catch (\Exception $e) {
                    // Fallback to standard store if optimization throws
                    try {
                        $receiptPath = $request->file('receipt_file')->store('payment-receipts', 'public');
                    } catch (\Exception $ex) {
                        // Fallback to local uploads directory if public disk fails
                        $file = $request->file('receipt_file');
                        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                        $file->move(public_path('uploads/payment-receipts'), $filename);
                        $receiptPath = 'uploads/payment-receipts/' . $filename;
                    }
                }
            }

            $receiptUrl = $receiptPath ? (str_starts_with($receiptPath, 'uploads/') ? asset($receiptPath) : asset('storage/' . $receiptPath)) : null;

            $metaData = [
                'sub_item' => $subItemId,
                'sub_item_label' => $subItemLabel,
                'payer_name' => $payerName,
                'payer_mobile' => $payerMobile,
                'tracking_code' => $tracking,
                'payment_date' => $date,
                'receipt_path' => $receiptPath,
                'receipt_url' => $receiptUrl,
            ];

            $payment->update([
                'type' => $method,
                'status' => \Modules\Booking\Entities\BookingPayment::STATUS_PAID,
                'gateway_ref' => $tracking ?: $payment->gateway_ref,
                'meta' => array_merge($payment->meta ?? [], array_filter($metaData)),
                'paid_at' => now(),
            ]);

            if ($payment->appointment && in_array($payment->appointment->status, [
                \Modules\Booking\Entities\Appointment::STATUS_PENDING_PAYMENT,
                \Modules\Booking\Entities\Appointment::STATUS_DRAFT
            ])) {
                $payment->appointment->update([
                    'status' => \Modules\Booking\Entities\Appointment::STATUS_PENDING,
                ]);
            }

            return back()->with('success', 'اطلاعات و رسید پرداخت شما با موفقیت ثبت شد و در انتظار تایید مدیریت است.');
        }
    }
}
