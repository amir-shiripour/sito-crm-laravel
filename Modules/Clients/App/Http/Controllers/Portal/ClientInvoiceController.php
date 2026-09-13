<?php

namespace Modules\Clients\App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\Services\App\Http\Models\Invoice;
use Modules\Services\App\Http\Models\Payment;
use Modules\Services\App\Http\Models\Status;
use Modules\Settings\Entities\Setting;
use App\Services\PaymentService as GlobalPaymentService;
use Nwidart\Modules\Facades\Module;
use Modules\Accounting\Services\AccountingEngine;
use Throwable;

class ClientInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $client = auth('client')->user();

        if (!class_exists(Invoice::class) || !Schema::hasTable('service_invoices')) {
            abort(404);
        }

        // 1. واکشی وضعیت‌های واقعی فاکتورهای خدمات از جدول وضعیت‌ها
        $excludeIds = Status::whereIn('id', [6, 7])->pluck('id')->toArray();
        $rawStatuses = Status::whereIn('type', ['payment', 'invoice'])
            ->whereNotIn('id', $excludeIds)
            ->orderBy('sort_order')
            ->get();

        // تجمیع وضعیت‌های هم‌نام (مثل لغو شده که هم در invoice و هم در payment هست)
        $filterStatuses = collect();
        foreach ($rawStatuses->groupBy('name') as $name => $statuses) {
            $ids = $statuses->pluck('id')->toArray();

            $key = match(true) {
                str_contains($name, 'انتظار') => 'pending',
                str_contains($name, 'معوقه') => 'overdue',
                str_contains($name, 'پرداخت شده') => 'paid',
                str_contains($name, 'لغو') => 'canceled',
                str_contains($name, 'ادغام') => 'merged',
                default => \Illuminate\Support\Str::slug($name) ?: ('status_' . $statuses->first()->id)
            };

            $priority = match(true) {
                str_contains($name, 'انتظار') => 1,
                str_contains($name, 'معوقه') => 2,
                str_contains($name, 'پرداخت شده') => 3,
                str_contains($name, 'لغو') => 4,
                str_contains($name, 'ادغام') => 5,
                default => 99
            };

            $count = Invoice::where('customer_id', $client->id)
                ->whereNotNull('invoice_number')
                ->whereIn('status_id', $ids)
                ->count();

            $filterStatuses->push([
                'name' => $name,
                'key' => $key,
                'ids' => $ids,
                'count' => $count,
                'priority' => $priority,
                'color' => $statuses->first()->color,
            ]);
        }

        $filterStatuses = $filterStatuses->sortBy('priority')->values();

        $totalCount = Invoice::where('customer_id', $client->id)->whereNotNull('invoice_number')->count();

        // کوئری فاکتورهای مشتری
        $query = Invoice::where('customer_id', $client->id)
            ->whereNotNull('invoice_number')
            ->with(['status', 'service', 'items', 'payments', 'order.service'])
            ->latest('id');

        $statusFilter = $request->query('status');
        $statusIdFilter = $request->query('status_id');

        if ($request->filled('status_id')) {
            $query->where('status_id', $statusIdFilter);
        } elseif ($request->filled('status') && $statusFilter !== 'all') {
            $matched = $filterStatuses->first(function($item) use ($statusFilter) {
                return $item['key'] === $statusFilter
                    || $item['name'] === $statusFilter
                    || in_array($statusFilter, array_map('strval', $item['ids']));
            });

            if ($matched) {
                $query->whereIn('status_id', $matched['ids']);
            } else {
                $query->whereHas('status', function($q) use ($statusFilter) {
                    $q->where('name', 'LIKE', "%{$statusFilter}%");
                });
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('service', function($sq) use ($search) {
                      $sq->where('name', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('order', function($oq) use ($search) {
                      $oq->where('order_number', 'LIKE', "%{$search}%")
                        ->orWhereHas('service', function($osq) use ($search) {
                            $osq->where('name', 'LIKE', "%{$search}%");
                        });
                  });
            });
        }

        $invoices = $query->paginate(12)->withQueryString();

        if (class_exists(\Modules\Services\App\Http\Models\Order::class) && Schema::hasTable('service_orders')) {
            $missingOrderInvoices = $invoices->getCollection()->filter(fn($inv) => !$inv->order && !empty($inv->meta['source_order_id']));
            if ($missingOrderInvoices->isNotEmpty()) {
                $sourceOrderIds = $missingOrderInvoices->map(fn($inv) => $inv->meta['source_order_id'])->unique()->filter()->values()->all();
                if (!empty($sourceOrderIds)) {
                    $extraOrders = \Modules\Services\App\Http\Models\Order::with('service')->whereIn('id', $sourceOrderIds)->get()->keyBy('id');
                    foreach ($missingOrderInvoices as $inv) {
                        $srcId = $inv->meta['source_order_id'];
                        if (isset($extraOrders[$srcId])) {
                            $inv->setRelation('order', $extraOrders[$srcId]);
                        }
                    }
                }
            }
        }
        $currentStatus = $statusFilter ?: ($statusIdFilter ? (string)$statusIdFilter : null);

        return view('clients::portal.invoices.index', compact(
            'invoices',
            'filterStatuses',
            'totalCount',
            'currentStatus'
        ));
    }

    public function show($id)
    {
        $client = auth('client')->user();

        if (!class_exists(Invoice::class) || !Schema::hasTable('service_invoices')) {
            abort(404);
        }

        $invoice = Invoice::where('customer_id', $client->id)
            ->with(['status', 'service', 'items.service', 'customer', 'payments.user'])
            ->findOrFail($id);

        // Update payment status if needed
        if (method_exists($invoice, 'updatePaymentStatus')) {
            $invoice->updatePaymentStatus(false);
            $invoice->load('status');
        }

        // Settings and Payment methods
        $settingsMap = Schema::hasTable('settings')
            ? Setting::query()->pluck('value', 'key')->toArray()
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

        // Linked order if any (handles both direct order invoice and recurring renewal invoice)
        $linkedOrder = $invoice->linked_order;
        if (!$linkedOrder && class_exists(\Modules\Services\App\Http\Models\Order::class) && Schema::hasTable('service_orders')) {
            $linkedOrder = \Modules\Services\App\Http\Models\Order::where('invoice_id', $invoice->id)->first();
        }


        // Allow partial payment setting
        $allowPartialPayment = ($settingsMap['services_allow_partial_payment'] ?? '0') === '1';

        return view('clients::portal.invoices.show', compact(
            'invoice',
            'availablePaymentMethods',
            'paymentSubItems',
            'bankAccounts',
            'linkedOrder',
            'allowPartialPayment'
        ));
    }

    public function print($id)
    {
        $client = auth('client')->user();

        if (!class_exists(Invoice::class) || !Schema::hasTable('service_invoices')) {
            abort(404);
        }

        $invoice = Invoice::where('customer_id', $client->id)
            ->with(['status', 'service', 'items.service', 'customer', 'payments'])
            ->findOrFail($id);

        $settings = Setting::pluck('value', 'key')->toArray();
        $currency = $settings['currency'] ?? 'toman';
        
        return view('services::invoices.print', compact('invoice', 'settings', 'currency'));
    }

    public function processPayment(Request $request, $id)
    {
        $client = auth('client')->user();

        if (!class_exists(Invoice::class) || !Schema::hasTable('service_invoices')) {
            abort(404);
        }

        $invoice = Invoice::where('customer_id', $client->id)->findOrFail($id);

        $isPaid = str_contains($invoice->status?->name ?? '', 'پرداخت شده');
        if ($isPaid) {
            return back()->with('error', 'این فاکتور قبلاً پرداخت شده است.');
        }

        $method = $request->input('payment_method');

        $request->validate([
            'payment_method' => 'required|string',
            'sub_item'       => 'nullable|string',
            'tracking_code'  => 'nullable|string|max:255',
            'payment_date'   => 'nullable|string|max:20',
            'payer_name'     => 'nullable|string|max:255',
            'payer_mobile'   => 'nullable|string|max:50',
            'receipt_file'   => ($method !== 'online') 
                ? 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240' 
                : 'nullable',
        ]);

        $settingsMap = Schema::hasTable('settings')
            ? Setting::query()->pluck('value', 'key')->toArray()
            : [];
        $allowPartial = ($settingsMap['services_allow_partial_payment'] ?? '0') === '1';

        $remaining = max(0, $invoice->total - $invoice->paid_amount);
        if ($remaining <= 0) {
            return back()->with('error', 'این فاکتور قبلاً به طور کامل تسویه شده است.');
        }

        $amount = $remaining;
        if ($allowPartial && $request->filled('amount')) {
            $rawAmount = (string)$request->input('amount');
            $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            $arabic  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
            $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            $cleanAmount = str_replace($persian, $english, $rawAmount);
            $cleanAmount = str_replace($arabic, $english, $cleanAmount);
            $parsedAmount = (float)preg_replace('/[^\d.]/', '', $cleanAmount);

            $invoiceCurrency = strtolower($invoice->currency ?? $settingsMap['currency'] ?? 'rial');
            $currencyLabel = in_array($invoiceCurrency, ['rial', 'irr', 'ریال']) ? 'ریال' : 'تومان';

            if ($parsedAmount <= 0) {
                return back()->withInput()->with('error', 'مبلغ پرداختی وارد شده باید بزرگتر از صفر باشد.');
            }

            if ($parsedAmount > $remaining) {
                return back()->withInput()->with('error', 'مبلغ پرداختی وارد شده (' . number_format($parsedAmount) . ' ' . $currencyLabel . ') نمی‌تواند بیشتر از مانده بدهی فاکتور (' . number_format($remaining) . ' ' . $currencyLabel . ') باشد.');
            }

            $amount = (int)round($parsedAmount);
        }

        $isPartial = $amount < $remaining;

        if ($method === 'online') {
            $gateway = $request->input('sub_item');
            if (empty($gateway)) {
                $gateway = $settingsMap['default_payment_gateway'] ?? 'zarinpal';
            }

            $invoiceCurrency = strtolower($invoice->currency ?? $settingsMap['currency'] ?? 'rial');
            $isRial = in_array($invoiceCurrency, ['rial', 'irr', 'ریال']);
            $amountInRials = $isRial ? (int)$amount : (int)($amount * 10);

            try {
                $globalPaymentService = new GlobalPaymentService($gateway);
                $description = "پرداخت صورت‌حساب شماره " . ($invoice->invoice_number ?: $invoice->proforma_invoice_number ?: $invoice->id) . ($isPartial ? ' (پرداخت جزئی)' : '');
                $email = $client->email;
                $mobile = $client->mobile ?? $client->phone;

                $callbackUrl = route('clients.invoices.verify', [
                    'invoice' => $invoice->id,
                    'gateway' => $gateway,
                ]);

                $result = $globalPaymentService->requestPaymentInRials(
                    $amountInRials,
                    $description,
                    $email,
                    $mobile,
                    $callbackUrl
                );

                if (!empty($result['success']) && !empty($result['payment_url'])) {
                    Payment::create([
                        'invoice_id'     => $invoice->id,
                        'user_id'        => null,
                        'amount'         => $amount,
                        'method'         => 'online',
                        'gateway'        => $gateway,
                        'paid_at'        => now(),
                        'transaction_id' => $result['authority'] ?? null,
                        'notes'          => 'پرداخت آنلاین از طریق ' . Payment::formatMethodName('online', $gateway) . ($isPartial ? ' (پرداخت جزئی)' : '') . ' | شناسه: ' . ($result['authority'] ?? '—'),
                        'status'         => 'pending',
                    ]);

                    return redirect()->away($result['payment_url']);
                }

                return back()->with('error', $result['message'] ?? 'خطا در برقراری ارتباط با درگاه پرداخت آنلاین.');
            } catch (\Throwable $e) {
                Log::error('[ClientInvoiceController] Online payment dispatch error: ' . $e->getMessage());
                return back()->with('error', 'خطا در شروع تراکنش آنلاین: ' . $e->getMessage());
            }
        } else {
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
                    try {
                        $receiptPath = $request->file('receipt_file')->store('payment-receipts', 'public');
                    } catch (\Exception $ex) {
                        $file = $request->file('receipt_file');
                        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                        $file->move(public_path('uploads/payment-receipts'), $filename);
                        $receiptPath = 'uploads/payment-receipts/' . $filename;
                    }
                }
            }

            $receiptUrl = $receiptPath ? (str_starts_with($receiptPath, 'uploads/') ? asset($receiptPath) : asset('storage/' . $receiptPath)) : null;

            $note = 'ثبت توسط مشتری در پرتال'
                . ($isPartial ? ' (پرداخت جزئی)' : '')
                . ($request->payer_name ? ' | پرداخت‌کننده: ' . $request->payer_name : '')
                . ($request->payer_mobile ? ' | شماره تماس: ' . $request->payer_mobile : '')
                . ($request->payment_date ? ' | تاریخ واریز: ' . $request->payment_date : '')
                . ($request->tracking_code ? ' | کد پیگیری: ' . $request->tracking_code : '')
                . ($receiptUrl ? ' | فایل پیوست: ' . $receiptUrl : '');

            Payment::create([
                'invoice_id'     => $invoice->id,
                'user_id'        => null,
                'amount'         => $amount,
                'method'         => $method,
                'gateway'        => $request->sub_item,
                'paid_at'        => now(),
                'transaction_id' => $request->tracking_code ?? uniqid('TR-'),
                'notes'          => $note,
                'status'         => 'pending',
            ]);

            if (method_exists($invoice, 'updatePaymentStatus')) {
                $invoice->updatePaymentStatus(false);
            }

            return back()->with('success', 'اطلاعات پرداخت و فیش واریزی با موفقیت ثبت شد و پس از بررسی و تایید کارشناسان در حساب شما منظور خواهد شد.');
        }
    }

    public function verifyOnlinePayment(Request $request, $id, $gateway = null)
    {
        $client = auth('client')->user();

        if (!class_exists(Invoice::class) || !Schema::hasTable('service_invoices')) {
            abort(404);
        }

        $invoiceQuery = Invoice::query();
        if ($client) {
            $invoiceQuery->where('customer_id', $client->id);
        }
        $invoice = $invoiceQuery->findOrFail($id);

        $gateway = $gateway ?: $request->query('gateway') ?: $request->input('gateway');

        // Extract tracking tokens across various Iranian gateways
        $authority = $request->input('Authority')
            ?: ($request->input('trackId')
            ?: ($request->input('RefId')
            ?: ($request->input('Token')
            ?: ($request->input('ResNum')
            ?: $request->input('RefNum')))));

        if (!$gateway) {
            $pendingPayment = Payment::where('invoice_id', $invoice->id)
                ->where('method', 'online')
                ->latest()
                ->first();
            $gateway = $pendingPayment?->gateway ?: Setting::where('key', 'default_payment_gateway')->value('value') ?: 'zarinpal';
        }

        $payment = Payment::where('invoice_id', $invoice->id)
            ->where('method', 'online')
            ->where(function ($q) use ($authority) {
                if ($authority) {
                    $q->where('transaction_id', $authority);
                } else {
                    $q->where('status', 'pending');
                }
            })
            ->latest()
            ->first();

        if (!$payment && $authority) {
            $payment = Payment::where('invoice_id', $invoice->id)
                ->where('transaction_id', $authority)
                ->first();
        }

        // Check user cancellation / NOK status across gateways
        if ($gateway === 'zibal') {
            $status = ((string)($request->input('success') ?? '')) === '1' ? 'OK' : 'NOK';
        } elseif ($gateway === 'behpardakht') {
            $resCode = (string)($request->input('ResCode') ?? '-1');
            $status = ($resCode === '0' || $resCode === '00') ? 'OK' : 'NOK';
        } elseif ($gateway === 'sep' || $gateway === 'saman') {
            $sepState = strtoupper((string)($request->input('State') ?? ''));
            $sepStatus = (int)($request->input('Status') ?? -1);
            $status = ($sepState === 'OK' && $sepStatus === 2) ? 'OK' : 'NOK';
        } else {
            $status = strtoupper((string)($request->input('Status') ?? ''));
        }

        if ($status === 'NOK' || $status === 'CANCELED') {
            $payment?->update(['status' => 'canceled']);
            return redirect()
                ->route('clients.invoices.show', $invoice->id)
                ->with('error', 'پرداخت آنلاین توسط کاربر لغو شد یا انجام نشد.');
        }

        try {
            $globalPaymentService = new GlobalPaymentService($gateway);

            $dataToVerify = $request->all();
            $invoiceCurrency = strtolower($invoice->currency ?? Setting::where('key', 'currency')->value('value') ?? 'rial');
            $isRial = in_array($invoiceCurrency, ['rial', 'irr', 'ریال']);
            $amountInRials = $isRial ? (int)($payment?->amount ?? $invoice->total) : (int)(($payment?->amount ?? $invoice->total) * 10);

            if ($gateway === 'zibal') {
                $dataToVerify['trackId'] = $authority;
                $dataToVerify['Amount'] = $amountInRials;
            } elseif ($gateway === 'behpardakht') {
                $dataToVerify['RefId'] = $authority;
                $dataToVerify['Amount'] = $amountInRials;
            } elseif ($gateway === 'sep' || $gateway === 'saman') {
                $dataToVerify['Token'] = $authority;
                $dataToVerify['RefNum'] = $request->input('RefNum');
                $dataToVerify['Amount'] = $amountInRials;
            } else {
                $dataToVerify['Authority'] = $authority;
                $dataToVerify['Amount'] = $amountInRials;
            }

            $verifyResult = $globalPaymentService->verifyPayment($dataToVerify);

            if ($verifyResult && ($verifyResult['success'] ?? false)) {
                $refId = $verifyResult['ref_id'] ?? $verifyResult['reference_id'] ?? $authority;

                DB::transaction(function () use ($invoice, $payment, $refId, $gateway) {
                    if ($payment) {
                        $payment->update([
                            'status' => 'paid',
                            'transaction_id' => $refId,
                            'paid_at' => now(),
                            'notes' => ($payment->notes ? $payment->notes . ' | ' : '') . 'تایید شده توسط درگاه | کد رهگیری: ' . $refId,
                        ]);
                    } else {
                        $payment = $invoice->payments()->create([
                            'user_id' => null,
                            'amount' => max(0, $invoice->total - $invoice->calculatePaidAmount()),
                            'method' => 'online',
                            'gateway' => $gateway,
                            'paid_at' => now(),
                            'transaction_id' => $refId,
                            'notes' => 'پرداخت آنلاین تایید شده | کد رهگیری: ' . $refId,
                            'status' => 'paid',
                        ]);
                    }

                    $invoice->paid_amount = $invoice->calculatePaidAmount();

                    $StatusModel = Status::class;
                    if ($invoice->isPaid()) {
                        $status = $StatusModel::where('name', 'پرداخت شده')->where('type', 'payment')->first()
                            ?? $StatusModel::where('name', 'LIKE', '%پرداخت شده%')->first();
                        if (!$invoice->paid_at) {
                            $invoice->paid_at = now();
                        }
                    } elseif ($invoice->isOverdue()) {
                        $status = $StatusModel::where('name', 'معوقه')->where('type', 'payment')->first();
                    } else {
                        $status = $StatusModel::where('name', 'در انتظار پرداخت')->where('type', 'payment')->first();
                    }

                    if ($status) {
                        $invoice->status_id = $status->id;
                    }

                    if ($invoice->isPaid() && Module::has('Accounting') && Module::isEnabled('Accounting')) {
                        try {
                            app(AccountingEngine::class)->recordFromServiceInvoice($invoice);
                        } catch (Throwable $e) {
                            Log::error('[AccountingEngine] Error on online invoice verification: ' . $e->getMessage());
                        }
                    }

                    if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
                        try {
                            app(AccountingEngine::class)->recordServicePayment($payment);
                        } catch (Throwable $e) {
                            Log::error('[AccountingEngine] Error recording service payment on verify: ' . $e->getMessage());
                        }
                    }

                    $invoice->save();

                    // Automatically activate linked orders
                    if (class_exists(\Modules\Services\App\Http\Controllers\InvoiceController::class)) {
                        app(\Modules\Services\App\Http\Controllers\InvoiceController::class)->syncOrdersForInvoice($invoice);
                    }
                });

                return redirect()
                    ->route('clients.invoices.show', $invoice->id)
                    ->with('success', 'پرداخت شما با موفقیت انجام شد و صورت‌حساب تسویه گردید. کد پیگیری: ' . $refId);
            } else {
                $payment?->update(['status' => 'canceled']);
                $errorMsg = $verifyResult['message'] ?? 'تراکنش توسط درگاه تایید نشد.';
                return redirect()
                    ->route('clients.invoices.show', $invoice->id)
                    ->with('error', 'خطا در تایید تراکنش بانکی: ' . $errorMsg);
            }
        } catch (\Throwable $e) {
            Log::error('Invoice online payment verify error: ' . $e->getMessage());
            return redirect()
                ->route('clients.invoices.show', $invoice->id)
                ->with('error', 'خطا در پردازش بازگشت از درگاه: ' . $e->getMessage());
        }
    }
}
