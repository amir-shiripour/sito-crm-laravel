<?php

namespace Modules\Accounting\App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Models\AccountingSetting;
use Modules\Accounting\App\Models\Invoice;
use Modules\Clients\Entities\Client;
use Morilog\Jalali\Jalalian;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;

class InvoiceCreate extends Component
{
    // اطلاعات اصلی صورت حساب
    public $client_id;
    public $invoice_number;
    public $issue_date;
    public $due_date;
    public $notes;

    // ردیف های صورت حساب
    public array $items = [];

    // مقادیر محاسباتی
    public $discount = 0;
    public $tax_rate = 0;

    // داده های کمکی و تنظیمات
    public $clients = [];
    public array $availableUnits = [];
    public string $numberingMode = 'auto';
    public bool $isNumberingLocked = true;
    public bool $isTaxLocked = true;

    public function mount(): void
    {
        $settings = AccountingSetting::all()->pluck('value', 'key')->mapWithKeys(function ($value, $key) {
            return [$key => json_decode($value, true)];
        });

        $this->numberingMode = $settings->get('numbering.mode', 'auto');
        $this->isNumberingLocked = ($this->numberingMode === 'auto');
        if ($this->isNumberingLocked) {
            $this->generateNextInvoiceNumber($settings);
        }

        $this->isTaxLocked = (bool) $settings->get('tax.enabled', false);
        $this->tax_rate = $this->isTaxLocked ? (float) $settings->get('tax.percentage', 0) : 0;

        $this->availableUnits = $settings->get('units.list', ['عدد', 'کیلوگرم', 'متر']);
        $this->clients = Client::select('id', 'full_name', 'username', 'national_code')->get();
        $this->issue_date = Jalalian::now()->format('Y/m/d');
        $this->due_date = Jalalian::now()->addDays(7)->format('Y/m/d');

        if (empty($this->items)) {
            $this->addItem();
        }
    }

    #[Computed]
    public function subtotal()
    {
        return collect($this->items)->sum(function ($item) {
            $quantity = (float)str_replace(',', '', $item['quantity'] ?? 0);
            $unitPrice = (float)str_replace(',', '', $item['unit_price'] ?? 0);
            return $quantity * $unitPrice;
        });
    }

    #[Computed]
    public function taxAmount()
    {
        $discountAmount = (float)str_replace(',', '', $this->discount ?? 0);
        $totalAfterDiscount = $this->subtotal() - $discountAmount;
        return ($totalAfterDiscount * (float)$this->tax_rate) / 100;
    }

    #[Computed]
    public function grandTotal()
    {
        $discountAmount = (float)str_replace(',', '', $this->discount ?? 0);
        return ($this->subtotal() - $discountAmount) + $this->taxAmount();
    }

    private function generateNextInvoiceNumber($settings): void
    {
        $includeYear = (bool) ($settings->get('numbering.include_year') ?? true);
        $prefix = $settings->get('numbering.prefix') ?? 'INV';
        $separator = $settings->get('numbering.separator') ?? '-';
        $length = (int) ($settings->get('numbering.length') ?? 4);

        $parts = [];
        if ($includeYear) $parts[] = Jalalian::now()->format('Y');
        if ($prefix) $parts[] = $prefix;

        $latestInvoice = Invoice::query()->latest('id')->first();
        $nextNumber = $latestInvoice ? ((int)preg_replace('/[^0-9]/', '', $latestInvoice->invoice_number)) + 1 : 1;

        $parts[] = str_pad((string)$nextNumber, $length, '0', STR_PAD_LEFT);
        $this->invoice_number = implode($separator, $parts);
    }

    public function addItem(): void
    {
        $this->items[] = ['description' => '', 'quantity' => 1, 'unit' => $this->availableUnits[0] ?? 'عدد', 'unit_price' => ''];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save()
    {
        // Clean up formatted numbers before validation
        $this->discount = str_replace(',', '', $this->discount);
        foreach ($this->items as $index => $item) {
            $this->items[$index]['unit_price'] = str_replace(',', '', $item['unit_price']);
            $this->items[$index]['quantity'] = str_replace(',', '', $item['quantity']);
        }

        $validatedData = $this->validate([
            'client_id' => 'required|exists:clients,id',
            'invoice_number' => ['required', 'string', 'max:255', Rule::unique('invoices')],
            'issue_date' => 'required|string',
            'due_date' => 'nullable|string',
            'notes' => 'nullable|string',
            'discount' => 'nullable|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($validatedData) {
                $invoice = Invoice::create([
                    'client_id' => $validatedData['client_id'],
                    'invoice_number' => $validatedData['invoice_number'],
                    'issue_date' => $validatedData['issue_date'],
                    'due_date' => $validatedData['due_date'],
                    'notes' => $validatedData['notes'],
                    'subtotal' => $this->subtotal(),
                    'discount' => (float)$validatedData['discount'],
                    'tax' => (float)$validatedData['tax_rate'],
                    'total_amount' => $this->grandTotal(),
                    'status' => 'unpaid',
                ]);

                // Prepare items with total_price for insertion
                $itemsToCreate = collect($validatedData['items'])->map(function ($item) {
                    $quantity = (float)$item['quantity'];
                    $unitPrice = (float)$item['unit_price'];
                    return array_merge($item, ['total_price' => $quantity * $unitPrice]);
                })->all();

                $invoice->items()->createMany($itemsToCreate);
            });

            session()->flash('success', 'صورت حساب با موفقیت ثبت شد.');
            return $this->redirect(route('admin.accounting.invoices.index'), navigate: true);

        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'text' => 'خطا در ذخیره سازی: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('accounting::livewire.invoice-create')
            ->layout('layouts.user', ['title' => 'صدور صورت حساب جدید']);
    }
}
