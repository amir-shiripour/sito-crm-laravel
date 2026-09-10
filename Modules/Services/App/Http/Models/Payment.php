<?php

namespace Modules\Services\App\Http\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $table = 'service_invoice_payments';

    protected $fillable = [
        'invoice_id',
        'user_id',
        'amount',
        'method',
        'gateway',
        'paid_at',
        'transaction_id',
        'notes',
        'status',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'integer',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Cache for payment settings to avoid repeated queries in the same request.
     */
    protected static ?array $cachedSettings = null;

    /**
     * Decode and format raw payment method/gateway strings to human-readable Persian names.
     */
    public static function formatMethodName(?string $method, ?string $gateway = null): string
    {
        if (empty($method) && empty($gateway)) {
            return '—';
        }

        $raw = trim((string)($method ?: $gateway));
        $lower = strtolower($raw);
        $checkGateway = strtolower(trim((string)$gateway ?: ''));

        // Load and cache settings once per request
        if (self::$cachedSettings === null) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                    self::$cachedSettings = \Illuminate\Support\Facades\DB::table('settings')
                        ->whereIn('key', ['pos_devices', 'bank_transfer_accounts', 'installment_types'])
                        ->pluck('value', 'key')
                        ->toArray();
                } else {
                    self::$cachedSettings = [];
                }
            } catch (\Throwable $e) {
                self::$cachedSettings = [];
            }
        }

        // 1. دستگاه کارتخوان (POS)
        // Matches e.g. pos-pos_1788859707755_t7evt, pos-1, pos_1788859707755_t7evt, pos
        if (str_starts_with($lower, 'pos-') || str_starts_with($lower, 'pos_') || $lower === 'pos') {
            $posId = ($lower === 'pos') ? null : preg_replace('/^pos[-_]/i', '', $raw);
            if ($posId) {
                $posDevices = json_decode(self::$cachedSettings['pos_devices'] ?? '[]', true) ?: [];
                foreach ($posDevices as $device) {
                    if (isset($device['id']) && (string)$device['id'] === (string)$posId) {
                        return 'کارتخوان ' . ($device['name'] ?? '');
                    }
                }
            }
            return 'دستگاه کارتخوان (POS)';
        }

        // 2. انتقال بانکی / کارت به کارت
        // Matches e.g. transfer-bank_1788859675199_7pqks, transfer-acc_0, transfer, card_to_card
        if (str_starts_with($lower, 'transfer-') || str_starts_with($lower, 'transfer_') || $lower === 'transfer' || $lower === 'card_to_card') {
            $accId = ($lower === 'transfer' || $lower === 'card_to_card') ? null : preg_replace('/^transfer[-_]/i', '', $raw);
            if ($accId) {
                $bankAccounts = json_decode(self::$cachedSettings['bank_transfer_accounts'] ?? '[]', true) ?: [];
                foreach ($bankAccounts as $account) {
                    if (isset($account['id']) && (string)$account['id'] === (string)$accId) {
                        $bankName = $account['bank_name'] ?? ($account['name'] ?? '');
                        return 'کارت به کارت (' . ($bankName ?: 'بانک') . ')';
                    }
                }
            }
            return 'کارت به کارت / واریز فیش';
        }

        // 3. درگاه پرداخت آنلاین
        $combined = $lower . ' ' . $checkGateway;
        if (str_contains($combined, 'zibal')) {
            return 'درگاه پرداخت زیبال';
        }
        if (str_contains($combined, 'zarinpal')) {
            return 'درگاه پرداخت زرین‌پال';
        }
        if (str_contains($combined, 'mellat') || str_contains($combined, 'behpardakht')) {
            return 'درگاه به‌پرداخت ملت';
        }
        if (str_contains($combined, 'sadad')) {
            return 'درگاه سداد (بانک ملی)';
        }
        if (str_contains($combined, 'saman')) {
            return 'درگاه سامان کیش';
        }
        if (str_contains($combined, 'parsian')) {
            return 'درگاه پارسیان';
        }
        if (str_contains($combined, 'payping')) {
            return 'درگاه پی‌پینگ';
        }
        if (str_contains($combined, 'idpay')) {
            return 'درگاه آیدی‌پی';
        }
        if (str_starts_with($lower, 'online-')) {
            return 'درگاه پرداخت آنلاین ' . ucfirst(substr($raw, 7));
        }
        if ($lower === 'online' || $lower === 'gateway' || $lower === 'required') {
            return 'درگاه پرداخت آنلاین';
        }

        // 4. نقدی
        if (str_starts_with($lower, 'cash-') || $lower === 'cash') {
            return 'پرداخت نقدی / حضوری';
        }

        // 5. پرداخت در محل
        if ($lower === 'cod') {
            return 'پرداخت در محل';
        }

        // 6. چک / حواله
        if (str_starts_with($lower, 'cheque-') || str_starts_with($lower, 'check-') || $lower === 'cheque' || $lower === 'check') {
            return 'چک / حواله';
        }

        // 7. اقساطی
        if (str_starts_with($lower, 'installment-') || $lower === 'installment') {
            $instId = ($lower === 'installment') ? null : preg_replace('/^installment[-_]/i', '', $raw);
            if ($instId) {
                $installmentTypes = json_decode(self::$cachedSettings['installment_types'] ?? '[]', true) ?: [];
                foreach ($installmentTypes as $item) {
                    if (isset($item['id']) && (string)$item['id'] === (string)$instId) {
                        return 'پرداخت اقساطی (' . ($item['title'] ?? '') . ')';
                    }
                }
            }
            return 'اقساطی / چک';
        }

        // 8. کیف پول
        if ($lower === 'wallet') {
            return 'کیف پول';
        }

        // 9. اعتبار
        if ($lower === 'credit') {
            return 'اعتبار حساب';
        }

        // Fallbacks for common keywords
        if (str_contains($lower, 'pos')) {
            return 'دستگاه کارتخوان (POS)';
        }
        if (str_contains($lower, 'transfer') || str_contains($lower, 'card')) {
            return 'کارت به کارت / انتقال بانکی';
        }

        return ucfirst($raw);
    }

    public function getMethodLabelAttribute(): string
    {
        return self::formatMethodName($this->method, $this->gateway);
    }
}
