<?php

namespace Modules\Clients\App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Modules\Services\App\Http\Models\Invoice;
use Modules\Services\App\Http\Models\Payment;
use Modules\Services\App\Http\Models\Status;
use Modules\Settings\Entities\Setting;

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
            ->with(['status', 'service', 'items', 'payments'])
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
                  });
            });
        }

        $invoices = $query->paginate(12)->withQueryString();
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

        // Linked order if any
        $linkedOrder = null;
        if (class_exists(\Modules\Services\App\Http\Models\Order::class) && Schema::hasTable('service_orders')) {
            $linkedOrder = \Modules\Services\App\Http\Models\Order::where('invoice_id', $invoice->id)->first();
        }

        return view('clients::portal.invoices.show', compact(
            'invoice',
            'availablePaymentMethods',
            'paymentSubItems',
            'bankAccounts',
            'linkedOrder'
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

        $remaining = max(0, $invoice->total - $invoice->paid_amount);
        $amount = $remaining > 0 ? $remaining : $invoice->total;

        if ($method === 'online') {
            return back()->with('error', 'درگاه آنلاین در حال حاضر فعال نیست؛ لطفاً از گزینه انتقال بانکی / کارت به کارت استفاده فرمایید.');
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

            return back()->with('success', 'اطلاعات پرداخت و فیش واریزی با موفقیت ثبت شد و پس از بررسی کارشناسان تایید خواهد شد.');
        }
    }
}
