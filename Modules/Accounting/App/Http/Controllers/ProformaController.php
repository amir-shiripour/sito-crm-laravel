<?php

namespace Modules\Accounting\App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Http\Requests\StoreProformaRequest;
use Modules\Accounting\App\Models\AccountingSetting;
use Modules\Accounting\App\Models\Proforma;
use Modules\Accounting\Entities\Invoice;
use Modules\Accounting\App\Services\CurrencyService;
use Modules\Clients\Entities\Client;

class ProformaController extends Controller
{
    public function index()
    {
        $proformas = Proforma::with('client')
            ->latest('issue_date')
            ->latest('id')
            ->paginate(20);

        return view('accounting::proformas.index', compact('proformas'));
    }

    public function create()
    {
        $clients = Client::select('id', 'full_name', 'username')->get();
        $numberingMode = AccountingSetting::getValue('proforma.numbering_mode', 'auto');
        $nextProformaNumber = ($numberingMode === 'auto') ? Proforma::getNextProformaNumber() : '';
        $units = AccountingSetting::getValue('units.list', ['عدد', 'کیلوگرم', 'متر', 'ساعت']);

        $taxEnabled = (bool) AccountingSetting::getValue('tax.enabled', false);
        $defaultTaxRate = $taxEnabled ? AccountingSetting::getValue('tax.percentage', 0) : 0;

        $roundingMode = AccountingSetting::getValue('tax.rounding_mode', 'none');
        $roundingAmount = AccountingSetting::getValue('tax.rounding_amount', 1000);

        // Pass currency info to the view
        $baseCurrency = CurrencyService::getBaseCurrency();

        return view('accounting::proformas.create', compact('clients', 'numberingMode', 'nextProformaNumber', 'units', 'defaultTaxRate', 'roundingMode', 'roundingAmount', 'baseCurrency'));
    }

    public function store(StoreProformaRequest $request)
    {
        $numberingMode = AccountingSetting::getValue('proforma.numbering_mode', 'auto');
        if ($numberingMode === 'auto') {
            $request->merge(['proforma_number' => Proforma::getNextProformaNumber()]);
        }

        $validatedData = $request->validated();

        try {
            DB::transaction(function () use ($validatedData) {
                $subtotal = 0;
                $itemsWithTotalPrice = [];
                $itemsDiscountTotal = 0;

                foreach ($validatedData['items'] as $item) {
                    // Convert input amounts to base Rial
                    $unitPriceInRial = CurrencyService::convertToBaseRial($item['unit_price']);
                    $itemDiscountInRial = CurrencyService::convertToBaseRial($item['discount'] ?? 0);

                    $itemsDiscountTotal += $itemDiscountInRial;
                    $totalPriceInRial = ($item['quantity'] * $unitPriceInRial) - $itemDiscountInRial;
                    $subtotal += ($item['quantity'] * $unitPriceInRial);

                    $itemsWithTotalPrice[] = array_merge($item, [
                        'unit_price' => $unitPriceInRial,
                        'discount' => $itemDiscountInRial,
                        'total_price' => $totalPriceInRial
                    ]);
                }

                $globalDiscountInRial = CurrencyService::convertToBaseRial($validatedData['discount'] ?? 0);
                $totalDiscount = $globalDiscountInRial + $itemsDiscountTotal;
                $tax = $validatedData['tax'] ?? 0;
                $taxableAmount = $subtotal - $totalDiscount;
                $taxAmount = ($taxableAmount * $tax) / 100;
                $total_amount = $taxableAmount + $taxAmount;

                $roundingMode = AccountingSetting::getValue('tax.rounding_mode', 'none');
                $roundingAmount = (float) AccountingSetting::getValue('tax.rounding_amount', 1000);

                if ($roundingMode !== 'none') {
                    // Rounding amount should also respect the currency, but it's saved as a plain number.
                    // We assume the rounding amount is entered in the display currency.
                    $roundingAmountInRial = CurrencyService::convertToBaseRial($roundingAmount);
                    if ($roundingMode === 'up' && $roundingAmountInRial > 0) {
                        $total_amount = ceil($total_amount / $roundingAmountInRial) * $roundingAmountInRial;
                    } elseif ($roundingMode === 'down' && $roundingAmountInRial > 0) {
                        $total_amount = floor($total_amount / $roundingAmountInRial) * $roundingAmountInRial;
                    }
                }

                $proforma = Proforma::create([
                    'client_id' => $validatedData['client_id'],
                    'proforma_number' => $validatedData['proforma_number'],
                    'issue_date' => $validatedData['issue_date'],
                    'due_date' => $validatedData['due_date'] ?? null,
                    'subtotal' => $subtotal,
                    'discount' => $globalDiscountInRial,
                    'tax' => $tax,
                    'total_amount' => $total_amount,
                    'status' => 'draft',
                    'notes' => $validatedData['notes'] ?? null,
                ]);

                $proforma->items()->createMany($itemsWithTotalPrice);
            });

            return redirect()->route('admin.accounting.proformas.index')->with('success', 'پیش فاکتور با موفقیت صادر شد.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['msg' => 'خطا در صدور پیش فاکتور: ' . $e->getMessage()]);
        }
    }

    public function show(Proforma $proforma)
    {
        $proforma->load(['client', 'items']);
        $sellerInfo = $this->getSellerInfo();
        return view('accounting::proformas.show', compact('proforma', 'sellerInfo'));
    }

    public function print(Proforma $proforma)
    {
        $proforma->load(['client', 'items']);
        $sellerInfo = $this->getSellerInfo();
        return view('accounting::proformas.print', compact('proforma', 'sellerInfo'));
    }

    public function edit(Proforma $proforma)
    {
        if ($proforma->status === 'accepted') {
            return redirect()->route('admin.accounting.proformas.index')->with('error', 'پیش فاکتور تایید شده قابل ویرایش نیست.');
        }
        $proforma->load('items');
        $clients = Client::select('id', 'full_name', 'username')->get();
        $units = AccountingSetting::getValue('units.list', ['عدد', 'کیلوگرم', 'متر', 'ساعت']);

        // Convert amounts for display
        $proforma->discount = CurrencyService::convertForDisplay($proforma->discount);
        foreach ($proforma->items as $item) {
            $item->unit_price = CurrencyService::convertForDisplay($item->unit_price);
            $item->discount = CurrencyService::convertForDisplay($item->discount);
        }

        $roundingMode = AccountingSetting::getValue('tax.rounding_mode', 'none');
        $roundingAmount = AccountingSetting::getValue('tax.rounding_amount', 1000);
        $baseCurrency = CurrencyService::getBaseCurrency();

        return view('accounting::proformas.edit', compact('proforma', 'clients', 'units', 'roundingMode', 'roundingAmount', 'baseCurrency'));
    }

    public function update(StoreProformaRequest $request, Proforma $proforma)
    {
        if ($proforma->status === 'accepted') {
            return redirect()->route('admin.accounting.proformas.index')->with('error', 'پیش فاکتور تایید شده قابل ویرایش نیست.');
        }

        $validatedData = $request->validated();

        try {
            DB::transaction(function () use ($validatedData, $proforma) {
                $subtotal = 0;
                $itemsWithTotalPrice = [];
                $itemsDiscountTotal = 0;

                foreach ($validatedData['items'] as $item) {
                    $unitPriceInRial = CurrencyService::convertToBaseRial($item['unit_price']);
                    $itemDiscountInRial = CurrencyService::convertToBaseRial($item['discount'] ?? 0);

                    $itemsDiscountTotal += $itemDiscountInRial;
                    $totalPriceInRial = ($item['quantity'] * $unitPriceInRial) - $itemDiscountInRial;
                    $subtotal += ($item['quantity'] * $unitPriceInRial);

                    $itemsWithTotalPrice[] = array_merge($item, [
                        'unit_price' => $unitPriceInRial,
                        'discount' => $itemDiscountInRial,
                        'total_price' => $totalPriceInRial
                    ]);
                }

                $globalDiscountInRial = CurrencyService::convertToBaseRial($validatedData['discount'] ?? 0);
                $totalDiscount = $globalDiscountInRial + $itemsDiscountTotal;
                $tax = $validatedData['tax'] ?? 0;
                $taxableAmount = $subtotal - $totalDiscount;
                $taxAmount = ($taxableAmount * $tax) / 100;
                $total_amount = $taxableAmount + $taxAmount;

                $roundingMode = AccountingSetting::getValue('tax.rounding_mode', 'none');
                $roundingAmount = (float) AccountingSetting::getValue('tax.rounding_amount', 1000);

                if ($roundingMode !== 'none') {
                    $roundingAmountInRial = CurrencyService::convertToBaseRial($roundingAmount);
                    if ($roundingMode === 'up' && $roundingAmountInRial > 0) {
                        $total_amount = ceil($total_amount / $roundingAmountInRial) * $roundingAmountInRial;
                    } elseif ($roundingMode === 'down' && $roundingAmountInRial > 0) {
                        $total_amount = floor($total_amount / $roundingAmountInRial) * $roundingAmountInRial;
                    }
                }

                $proforma->update([
                    'client_id' => $validatedData['client_id'],
                    'proforma_number' => $validatedData['proforma_number'],
                    'issue_date' => $validatedData['issue_date'],
                    'due_date' => $validatedData['due_date'] ?? null,
                    'subtotal' => $subtotal,
                    'discount' => $globalDiscountInRial,
                    'tax' => $tax,
                    'total_amount' => $total_amount,
                    'notes' => $validatedData['notes'] ?? null,
                ]);

                $proforma->items()->delete();
                $proforma->items()->createMany($itemsWithTotalPrice);
            });

            return redirect()->route('admin.accounting.proformas.index')->with('success', 'پیش فاکتور با موفقیت ویرایش شد.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['msg' => 'خطا در ویرایش پیش فاکتور: ' . $e->getMessage()]);
        }
    }

    public function destroy(Proforma $proforma)
    {
        if ($proforma->status === 'accepted') {
            return back()->withErrors(['msg' => 'پیش فاکتور تایید شده قابل حذف نیست.']);
        }
        $proforma->items()->delete();
        $proforma->delete();
        return redirect()->route('admin.accounting.proformas.index')->with('success', 'پیش فاکتور با موفقیت حذف شد.');
    }

    public function convertToInvoice(Proforma $proforma)
    {
        if ($proforma->status !== 'sent' && $proforma->status !== 'draft') {
            return back()->withErrors(['msg' => 'این پیش فاکتور قبلاً تایید یا لغو شده است.']);
        }

        try {
            DB::transaction(function () use ($proforma) {
                $invoice = Invoice::create([
                    'client_id' => $proforma->client_id,
                    'invoice_number' => null, // پیش‌نویس
                    'issue_date' => now(),
                    'due_date' => $proforma->due_date,
                    'subtotal' => $proforma->subtotal,
                    'discount' => $proforma->discount,
                    'tax' => $proforma->tax,
                    'total' => $proforma->total_amount ?? $proforma->total,
                    'status' => 'draft',
                    'notes' => "تبدیل شده از پیش فاکتور شماره " . $proforma->proforma_number . "\n" . $proforma->notes,
                ]);

                $items = $proforma->items->map(function ($item) {
                    return [
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'discount' => $item->discount,
                        'total' => $item->total_price ?? ($item->quantity * $item->unit_price),
                        'unit_type' => $item->unit_type ?? 'عدد',
                    ];
                });
                $invoice->items()->createMany($items->toArray());

                $proforma->update(['status' => 'converted']);
            });

            return redirect()->route('admin.accounting.invoices.index')->with('success', 'پیش فاکتور با موفقیت به فاکتور تبدیل شد.');

        } catch (\Exception $e) {
            return back()->withErrors(['msg' => 'خطا در تبدیل به فاکتور: ' . $e->getMessage()]);
        }
    }

    private function getSellerInfo(): array
    {
        return [
            'name' => AccountingSetting::getValue('appearance.seller_name', ''),
            'national_id' => AccountingSetting::getValue('appearance.national_id', ''),
            'economic_number' => AccountingSetting::getValue('appearance.economic_number', ''),
            'registration_number' => AccountingSetting::getValue('appearance.registration_number', ''),
            'province_city' => AccountingSetting::getValue('appearance.province_city', ''),
            'address' => AccountingSetting::getValue('appearance.address', ''),
            'phone_fax' => AccountingSetting::getValue('appearance.phone_fax', ''),
            'postal_code' => AccountingSetting::getValue('appearance.postal_code', ''),
            'custom_fields' => AccountingSetting::getValue('appearance.custom_fields', []),
            'stamp_signature_image' => AccountingSetting::getValue('appearance.stamp_signature_image', ''),
            'stamp_signature_width' => AccountingSetting::getValue('appearance.stamp_signature_width', ''),
            'invoice_template' => AccountingSetting::getValue('appearance.invoice_template', 'standard'),
        ];
    }
}
