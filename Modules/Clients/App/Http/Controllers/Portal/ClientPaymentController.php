<?php

namespace Modules\Clients\App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Modules\Market\App\Models\Order as MarketOrder; // 💡 مسیر صحیح جایگزین شد

class ClientPaymentController extends Controller
{
    public function index(Request $request)
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

        // 3. Fetch Service Invoices (صورت‌حساب‌های خدمات - ثبت فاکتور به عنوان رسید رسمی)
        if (class_exists(\Modules\Services\App\Http\Models\Invoice::class) && Schema::hasTable('service_invoices')) {
            $serviceInvoices = \Modules\Services\App\Http\Models\Invoice::where('customer_id', $client->id)
                ->whereNotNull('invoice_number')
                ->with(['status', 'service', 'payments'])
                ->get()
                ->filter(function($inv) {
                    return !$inv->isMerged();
                })
                ->map(function($inv) {
                    $isPaid = $inv->isPaid();
                    $isCanceled = $inv->isCanceled();
                    $status = match(true) {
                        $isPaid => 'PAID',
                        $isCanceled => 'CANCELED',
                        default => 'PENDING',
                    };

                    $date = $inv->paid_at 
                        ?: ($inv->issue_date ? \Carbon\Carbon::parse($inv->issue_date) : $inv->created_at);

                    return (object)[
                        'id' => $inv->id,
                        'ref_id' => $inv->invoice_number,
                        'type' => 'invoice',
                        'type_label' => 'صورت‌حساب خدمات',
                        'title' => 'صورت‌حساب #' . $inv->invoice_number . (optional($inv->service)->name ? ' (' . optional($inv->service)->name . ')' : ''),
                        'amount' => $inv->total,
                        'status' => $status,
                        'date' => $date,
                        'payment_record' => $inv,
                        'invoice_id' => $inv->id,
                    ];
                });
            $allPayments = $allPayments->merge($serviceInvoices);
        }

        // Total counts for filter tabs
        $totalCount = $allPayments->count();
        $paidCount = $allPayments->filter(fn($p) => strtoupper($p->status) === 'PAID')->count();
        $pendingCount = $allPayments->filter(fn($p) => strtoupper($p->status) === 'PENDING')->count();
        $canceledCount = $allPayments->filter(fn($p) => in_array(strtoupper($p->status), ['CANCELED', 'CANCELLED', 'FAILED', 'REFUNDED']))->count();

        // Apply Status Filter
        if ($request->filled('status') && $request->status !== 'all') {
            $statusFilter = strtolower($request->status);
            if ($statusFilter === 'paid') {
                $allPayments = $allPayments->filter(fn($p) => strtoupper($p->status) === 'PAID');
            } elseif ($statusFilter === 'pending') {
                $allPayments = $allPayments->filter(fn($p) => strtoupper($p->status) === 'PENDING');
            } elseif (in_array($statusFilter, ['canceled', 'failed'])) {
                $allPayments = $allPayments->filter(fn($p) => in_array(strtoupper($p->status), ['CANCELED', 'CANCELLED', 'FAILED', 'REFUNDED']));
            }
        }

        // Apply Search Filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $allPayments = $allPayments->filter(function($p) use ($search) {
                return str_contains((string)$p->ref_id, $search)
                    || (isset($p->title) && mb_stripos((string)$p->title, $search) !== false)
                    || (isset($p->type_label) && mb_stripos((string)$p->type_label, $search) !== false);
            });
        }

        // Sort by date descending
        $allPayments = $allPayments->sortByDesc('date')->values();

        // Paginate payments
        $perPage = 12;
        $page = Paginator::resolveCurrentPage() ?: 1;
        $allPayments = new LengthAwarePaginator(
            $allPayments->forPage($page, $perPage)->values(),
            $allPayments->count(),
            $perPage,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'query' => request()->query(),
            ]
        );

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

        return view('clients::portal.payments.index', compact(
            'allPayments',
            'bookingCurrencyUnit',
            'bookingCurrencyLabel',
            'totalCount',
            'paidCount',
            'pendingCount',
            'canceledCount'
        ));
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
            if (($settingsMap['sep_status'] ?? '') === 'active') {
                $onlineGateways[] = ['id' => 'sep', 'label' => 'درگاه سامان کیش (سپ)'];
            }

            $rawAccounts = is_string($settingsMap['bank_transfer_accounts'] ?? null) 
                ? (json_decode($settingsMap['bank_transfer_accounts'], true) ?: []) 
                : (is_array($settingsMap['bank_transfer_accounts'] ?? null) ? $settingsMap['bank_transfer_accounts'] : []);

            // Filter active bank accounts for clients
            $activeBankAccounts = array_filter($rawAccounts, function($acc) {
                if (!isset($acc['is_active'])) {
                    return true;
                }
                return filter_var($acc['is_active'], FILTER_VALIDATE_BOOLEAN);
            });

            $bankAccounts = array_values(array_map(function($acc, $index) {
                return [
                    'id' => $acc['id'] ?? ($acc['bank_name'] ?? ('acc_' . $index)),
                    'bank_name' => $acc['bank_name'] ?? 'بانک',
                    'owner_name' => $acc['owner_name'] ?? ($acc['owner'] ?? ''),
                    'card_number' => !empty($acc['card_number']) ? preg_replace('/[^0-9]/', '', $acc['card_number']) : '',
                    'account_number' => $acc['account_number'] ?? '',
                    'iban' => $acc['iban'] ?? '',
                ];
            }, $activeBankAccounts, array_keys($activeBankAccounts)));

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
            if (in_array('transfer', $activeMethods) && ($settingsMap['bank_transfer_status'] ?? '') === 'active' && !empty($bankAccounts)) {
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

        if ($type === 'invoice_payment') {
            if (class_exists(\Modules\Services\App\Http\Models\Payment::class) && Schema::hasTable('service_invoice_payments')) {
                $pay = \Modules\Services\App\Http\Models\Payment::whereHas('invoice', function($q) use ($client) {
                    $q->where('customer_id', $client->id);
                })->findOrFail($id);
                return redirect()->route('client.invoices.show', $pay->invoice_id);
            }
            abort(404);
        }

        if ($type === 'service' || $type === 'invoice' || $type === 'invoice_due') {
            if (!class_exists(\Modules\Services\App\Http\Models\Invoice::class) || !Schema::hasTable('service_invoices')) {
                abort(404);
            }

            $invoice = \Modules\Services\App\Http\Models\Invoice::where('customer_id', $client->id)
                ->with(['status', 'service', 'items.service', 'customer', 'payments.user'])
                ->findOrFail($id);

            // Update payment status if needed
            if (method_exists($invoice, 'updatePaymentStatus')) {
                $invoice->updatePaymentStatus(false);
                $invoice->load('status');
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
            if (($settingsMap['sep_status'] ?? '') === 'active') {
                $onlineGateways[] = ['id' => 'sep', 'label' => 'درگاه سامان کیش (سپ)'];
            }

            $rawAccounts = is_string($settingsMap['bank_transfer_accounts'] ?? null) 
                ? (json_decode($settingsMap['bank_transfer_accounts'], true) ?: []) 
                : (is_array($settingsMap['bank_transfer_accounts'] ?? null) ? $settingsMap['bank_transfer_accounts'] : []);

            $activeBankAccounts = array_filter($rawAccounts, function($acc) {
                if (!isset($acc['is_active'])) {
                    return true;
                }
                return filter_var($acc['is_active'], FILTER_VALIDATE_BOOLEAN);
            });

            $bankAccounts = array_values(array_map(function($acc, $index) {
                return [
                    'id' => $acc['id'] ?? ($acc['bank_name'] ?? ('acc_' . $index)),
                    'bank_name' => $acc['bank_name'] ?? 'بانک',
                    'owner_name' => $acc['owner_name'] ?? ($acc['owner'] ?? ''),
                    'card_number' => !empty($acc['card_number']) ? preg_replace('/[^0-9]/', '', $acc['card_number']) : '',
                    'account_number' => $acc['account_number'] ?? '',
                    'iban' => $acc['iban'] ?? '',
                ];
            }, $activeBankAccounts, array_keys($activeBankAccounts)));

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

            $availablePaymentMethods = [];
            if (in_array('online', $activeMethods) && !empty($onlineGateways)) {
                $availablePaymentMethods['online'] = $allMethodLabels['online'];
            }
            if (in_array('transfer', $activeMethods) && ($settingsMap['bank_transfer_status'] ?? '') === 'active' && !empty($bankAccounts)) {
                $availablePaymentMethods['transfer'] = $allMethodLabels['transfer'];
            }
            if (in_array('pos', $activeMethods) && ($settingsMap['pos_status'] ?? '') === 'active') {
                $availablePaymentMethods['pos'] = $allMethodLabels['pos'];
            }
            if (in_array('installment', $activeMethods) && ($settingsMap['installment_status'] ?? '') === 'active') {
                $availablePaymentMethods['installment'] = $allMethodLabels['installment'];
            }
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

            // Linked order if any
            $linkedOrder = null;
            if (class_exists(\Modules\Services\App\Http\Models\Order::class) && Schema::hasTable('service_orders')) {
                $linkedOrder = \Modules\Services\App\Http\Models\Order::where('invoice_id', $invoice->id)->first();
            }

            return view('clients::portal.payments.show_invoice', compact(
                'invoice',
                'availablePaymentMethods',
                'paymentSubItems',
                'bankAccounts',
                'linkedOrder'
            ));
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
                || ($settingsMap['behpardakht_status'] ?? '') === 'active'
                || ($settingsMap['sep_status'] ?? '') === 'active';
            $isMethodValid = in_array('online', $activeMethods) && $hasActiveGateway;
        } elseif ($method === 'pos') {
            $isMethodValid = in_array('pos', $activeMethods) && ($settingsMap['pos_status'] ?? '') === 'active';
        } elseif ($method === 'transfer') {
            $rawAccounts = is_string($settingsMap['bank_transfer_accounts'] ?? null) 
                ? (json_decode($settingsMap['bank_transfer_accounts'], true) ?: []) 
                : (is_array($settingsMap['bank_transfer_accounts'] ?? null) ? $settingsMap['bank_transfer_accounts'] : []);
            $hasActiveAccounts = false;
            foreach ($rawAccounts as $acc) {
                if (!isset($acc['is_active']) || filter_var($acc['is_active'], FILTER_VALIDATE_BOOLEAN)) {
                    $hasActiveAccounts = true;
                    break;
                }
            }
            $isMethodValid = in_array('transfer', $activeMethods) 
                && ($settingsMap['bank_transfer_status'] ?? '') === 'active' 
                && $hasActiveAccounts;
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
                $gateway = $request->input('sub_item'); // e.g. zarinpal, zibal, behpardakht
                session(['client_payment_return_url' => url()->previous()]);
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

            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $settingsMap = \Modules\Settings\Entities\Setting::query()->pluck('value', 'key')->toArray();
                $rawAccounts = is_string($settingsMap['bank_transfer_accounts'] ?? null) 
                    ? (json_decode($settingsMap['bank_transfer_accounts'], true) ?: []) 
                    : (array) ($settingsMap['bank_transfer_accounts'] ?? []);
                
                $matchedAcc = null;
                if ($subItemId) {
                    foreach ($rawAccounts as $acc) {
                        $accId = $acc['id'] ?? ($acc['bank_name'] ?? '');
                        if ($accId == $subItemId || (!empty($acc['bank_name']) && $acc['bank_name'] == $subItemId)) {
                            $matchedAcc = $acc;
                            break;
                        }
                    }
                }

                if (!$matchedAcc && $method === 'transfer' && !empty($rawAccounts)) {
                    foreach ($rawAccounts as $acc) {
                        if (!isset($acc['is_active']) || filter_var($acc['is_active'], FILTER_VALIDATE_BOOLEAN)) {
                            $matchedAcc = $acc;
                            $subItemId = $acc['id'] ?? ($acc['bank_name'] ?? '');
                            break;
                        }
                    }
                }

                if ($matchedAcc) {
                    $bankName = $matchedAcc['bank_name'] ?? 'بانک';
                    $ownerName = $matchedAcc['owner_name'] ?? ($matchedAcc['owner'] ?? '');
                    $cardNumber = !empty($matchedAcc['card_number']) ? $matchedAcc['card_number'] : '';
                    $iban = !empty($matchedAcc['iban']) ? $matchedAcc['iban'] : '';
                    $subItemLabel = $bankName . (!empty($ownerName) ? ' - ' . $ownerName : '') . (!empty($cardNumber) ? ' (' . $cardNumber . ')' : (!empty($iban) ? ' (' . $iban . ')' : ''));
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
                'status' => \Modules\Booking\Entities\BookingPayment::STATUS_PENDING,
                'gateway_ref' => $tracking ?: $payment->gateway_ref,
                'meta' => array_merge($payment->meta ?? [], array_filter($metaData)),
                'paid_at' => null,
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
