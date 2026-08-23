<?php

namespace Modules\Services\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Morilog\Jalali\Jalalian;
use Modules\Clients\Entities\Client;
use Modules\Services\App\Http\Models\Order;
use Modules\Services\App\Http\Models\Status;
use Modules\Services\App\Http\Models\Service;
use Modules\Services\App\Http\Models\Invoice;
use Modules\Services\App\Http\Models\InvoiceItem;
use Modules\Services\App\Http\Models\ActivityLog;
use Modules\Settings\Entities\Setting;
use Modules\Workflows\Services\WorkflowEngine;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        // $this->authorize('viewAny', Order::class);

        $query = Order::with(['customer', 'status', 'service', 'invoice.payments'])
            ->when($request->search, function ($q, $s) {
                $q->where('order_number', 'like', "%$s%")
                    ->orWhere('client_name', 'like', "%$s%")
                    ->orWhereHas('service', function ($q) use ($s) {
                        $q->where('name', 'like', "%$s%");
                    })
                    ->orWhereHas('invoice', function ($q) use ($s) {
                        $q->where('invoice_number', 'like', "%$s%")
                            ->orWhere('proforma_invoice_number', 'like', "%$s%");
                    });
            })
            ->when($request->status_id, function ($q, $v) {
                $q->where('status_id', $v);
            })
            ->when($request->service_id, function ($q, $v) {
                $q->where('service_id', $v);
            })
            ->when($request->customer_id, function ($q, $v) {
                $q->where('customer_id', $v);
            });

        $orders = $query->orderBy('invoice_id', 'desc')->orderBy('id', 'asc')->paginate(20)->withQueryString();

        $statuses = Status::where('type', 'order')->orderBy('sort_order')->get();
        $services = Service::orderBy('name')->get();
        $customers = Client::orderBy('full_name')->get();
        $currency = Setting::where('key', 'currency')->value('value') ?? 'toman';

        return view('services::orders.index', compact('orders', 'statuses', 'services', 'customers', 'currency'));
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'status', 'service', 'invoice.payments']);

        $statuses = Status::where('type', 'order')->orderBy('sort_order')->get();
        $currency = Setting::where('key', 'currency')->value('value') ?? 'toman';

        return view('services::orders.show', compact('order', 'statuses', 'currency'));
    }

    public function update(Request $request, Order $order)
    {
        if ($request->has('renewal_price')) {
            $request->merge(['renewal_price' => str_replace(',', '', $request->renewal_price)]);
        }

        $validated = $request->validate([
            'status_id' => 'nullable|exists:services_statuses,id',
            'renewal_date' => 'nullable|string',
            'renewal_price_type' => 'required|in:auto,manual',
            'renewal_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        if ($request->filled('renewal_date')) {
            try {
                $englishDate = str_replace(
                    ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'],
                    ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
                    $request->renewal_date
                );
                $validated['renewal_date'] = Jalalian::fromFormat('Y/m/d', $englishDate)->toCarbon();
            } catch (\Exception $e) {
                unset($validated['renewal_date']);
            }
        }

        if ($validated['renewal_price_type'] === 'auto') {
            $validated['renewal_price'] = $order->renewal_price;
        }

        $order->update($validated);

        return back()->with('success', 'اطلاعات سفارش با موفقیت بروزرسانی شد.');
    }

    public function createRenewalInvoice(Request $request, Order $order)
    {
        if (!$order->billing_cycle) {
            return back()->with('error', 'این سفارش یک سرویس دوره‌ای نیست یا دوره تمدید برای آن مشخص نشده است.');
        }

        $service = $order->service_id ? Service::find($order->service_id) : null;

        // Determine renewal price
        $isRenewalManual = ($order->renewal_price_type ?? 'auto') === 'manual';
        $renewalPrice = $isRenewalManual
            ? (float)($order->renewal_price ?? 0)
            : (($service && isset($service->renewal_prices[$order->billing_cycle]))
                ? (float)$service->renewal_prices[$order->billing_cycle]
                : (float)($order->renewal_price ?? 0));

        if ($renewalPrice <= 0) {
            $renewalPrice = (float)($order->total_amount ?: ($order->first_payment_amount ?: ($service?->base_price ?: 0)));
        }

        // Determine tax percent
        $taxPercent = 0;
        if ($order->invoice) {
            $taxPercent = (float)($order->invoice->tax_percent ?? 0);
            if ($taxPercent == 0 && $order->invoice->items->isNotEmpty()) {
                $matchingItem = $order->invoice->items->firstWhere('service_id', $order->service_id);
                if ($matchingItem && $matchingItem->tax_percent > 0) {
                    $taxPercent = (float)$matchingItem->tax_percent;
                }
            }
        } else {
            $settings = Setting::pluck('value', 'key')->toArray();
            $taxMode = $settings['services_tax_mode'] ?? 'invoice';
            if ($taxMode !== 'none') {
                $taxPercent = (float)($settings['services_default_tax_rate'] ?? 0);
            }
        }

        $quantity = 1;
        $itemSubtotal = $renewalPrice * $quantity;
        $itemTax = $taxPercent > 0 ? round(($itemSubtotal * $taxPercent) / 100) : 0;
        $itemTotal = $itemSubtotal + $itemTax;

        $currentRenewalDate = $order->renewal_date ? Carbon::parse($order->renewal_date) : now();
        $renewalDateStr = $currentRenewalDate->format('Y-m-d');
        $renewalInvoiceNumber = Invoice::generateNumber();

        $pendingStatus = Status::where('type', 'payment')->where('name', 'در انتظار پرداخت')->first()
            ?? Status::where('type', 'invoice')->where('name', 'در انتظار پرداخت')->first()
            ?? Status::where('type', 'payment')->first()
            ?? $order->status_id;

        $settings = Setting::pluck('value', 'key')->toArray();
        $currency = $settings['currency'] ?? $settings['payment_currency'] ?? $order->invoice?->currency ?? 'toman';

        $billingCycleLabels = [
            'monthly' => 'ماهانه',
            'quarterly' => 'فصلی',
            'semi_annual' => 'شش ماهه',
            'annual' => 'سالانه',
        ];
        $cycleLabel = $billingCycleLabels[$order->billing_cycle] ?? $order->billing_cycle;

        // Calculate next renewal date based on subscription type (billing cycle)
        $nextRenewalDate = null;
        if (class_exists(Jalalian::class)) {
            try {
                $jalali = Jalalian::fromCarbon($currentRenewalDate);
                switch ($order->billing_cycle) {
                    case 'monthly':     $nextJalali = $jalali->addMonths(1); break;
                    case 'quarterly':   $nextJalali = $jalali->addMonths(3); break;
                    case 'semi_annual': $nextJalali = $jalali->addMonths(6); break;
                    case 'annual':      $nextJalali = $jalali->addYears(1); break;
                    default:            $nextJalali = null; break;
                }
                if (isset($nextJalali)) {
                    $nextRenewalDate = $nextJalali->toCarbon()->format('Y-m-d');
                }
            } catch (\Throwable $e) {}
        }
        if (!$nextRenewalDate) {
            switch ($order->billing_cycle) {
                case 'monthly':     $nextRenewalDate = $currentRenewalDate->copy()->addMonth()->format('Y-m-d'); break;
                case 'quarterly':   $nextRenewalDate = $currentRenewalDate->copy()->addMonths(3)->format('Y-m-d'); break;
                case 'semi_annual': $nextRenewalDate = $currentRenewalDate->copy()->addMonths(6)->format('Y-m-d'); break;
                case 'annual':      $nextRenewalDate = $currentRenewalDate->copy()->addYear()->format('Y-m-d'); break;
                default:            $nextRenewalDate = $currentRenewalDate->copy()->addMonth()->format('Y-m-d'); break;
            }
        }

        $renewalInvoice = null;

        DB::transaction(function () use (
            &$renewalInvoice, $order, $service, $renewalInvoiceNumber,
            $pendingStatus, $renewalDateStr, $nextRenewalDate, $taxPercent, $itemSubtotal,
            $itemTax, $itemTotal, $currency, $cycleLabel, $renewalPrice,
            $quantity
        ) {
            $clientName = $order->client_name ?: ($order->customer?->full_name ?: '—');
            $clientPhone = $order->client_phone ?: $order->customer?->phone;
            $clientEmail = $order->client_email ?: $order->customer?->email;

            $renewalInvoice = Invoice::create([
                'invoice_number' => $renewalInvoiceNumber,
                'service_id' => $order->service_id,
                'customer_id' => $order->customer_id,
                'created_by' => auth()->id() ?? $order->created_by ?? 1,
                'status_id' => $pendingStatus?->id ?? $order->status_id,
                'client_name' => $clientName,
                'client_phone' => $clientPhone,
                'client_email' => $clientEmail,
                'issue_date' => $renewalDateStr,
                'due_date' => $nextRenewalDate,
                'tax_percent' => $taxPercent,
                'subtotal' => (int)$itemSubtotal,
                'discount_amount' => 0,
                'tax_amount' => (int)$itemTax,
                'total' => (int)$itemTotal,
                'paid_amount' => 0,
                'currency' => $currency,
                'notes' => 'فاکتور تمدید دوره‌ای برای سفارش ' . ($order->order_number ?? '') . ' (' . ($service->name ?? $order->notes ?? '') . ') - صدور دستی',
                'meta' => [
                    'is_renewal' => true,
                    'created_by_manual_renewal' => true,
                    'source_invoice_id' => $order->invoice_id,
                    'source_order_id' => $order->id,
                    'billing_period' => $order->billing_cycle,
                    'skip_auto_renewal_invoice' => true,
                ],
            ]);

            $serviceName = $service?->name ?? $order->notes ?? 'تمدید سرویس';

            $renewalInvoice->items()->create([
                'service_id' => $order->service_id,
                'custom_service_name' => $serviceName,
                'description' => 'تمدید دوره سرویس (' . $cycleLabel . ')',
                'unit' => 'عدد',
                'quantity' => $quantity,
                'unit_price' => (int)$renewalPrice,
                'discount' => 0,
                'tax_percent' => $taxPercent,
                'tax_amount' => (int)$itemTax,
                'total' => (int)$itemTotal,
                'meta' => [
                    'billing_period' => $order->billing_cycle,
                    'is_renewal_item' => true,
                ],
            ]);

            // Advance Order's renewal_date to the NEXT billing cycle
            if ($nextRenewalDate) {
                $order->update(['renewal_date' => $nextRenewalDate]);
            }

            ActivityLog::log(
                'صدور دستی فاکتور تمدید',
                $renewalInvoice,
                "فاکتور تمدید دوره‌ای دستی شماره {$renewalInvoice->invoice_number} برای سفارش {$order->order_number} صادر گردید."
            );

            // Trigger invoice_created workflow on the renewal invoice so other workflows continue to run
            if (class_exists(WorkflowEngine::class)) {
                try {
                    app(WorkflowEngine::class)->start('invoice_created', 'INVOICE', $renewalInvoice->id, [
                        'source_order_id' => $order->id,
                        'is_renewal' => true,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('[Workflows] Error starting invoice_created workflow for manual renewal: ' . $e->getMessage());
                }
            }
        });

        $order->refresh();
        $nextJalaliStr = $order->renewal_date ? Jalalian::fromCarbon(Carbon::parse($order->renewal_date))->format('Y/m/d') : '—';

        return back()->with('success', "فاکتور تمدید دستی با شماره {$renewalInvoice->invoice_number} با موفقیت صادر شد و تاریخ سررسید تمدید بعدی سفارش به {$nextJalaliStr} منتقل گردید.");
    }

    public function create()
    {
        return redirect()->route('services.invoices.create', ['type' => 'invoice']);
    }

    public function store(Request $request)
    {
    }

    public function edit($id)
    {
    }

    public function destroy($id)
    {
    }
}
