<?php

namespace Modules\Wallet\App\Enums;

enum TransactionType: string
{
    case DEPOSIT = 'deposit';       // واریز / شارژ
    case WITHDRAW = 'withdraw';     // برداشت / تسویه
    case PAYMENT = 'payment';       // پرداخت فاکتور / خدمات
    case REFUND = 'refund';         // بازگشت وجه
    case TRANSFER = 'transfer';     // جابجایی بین کیف‌پول‌ها
    case COMMISSION = 'commission'; // پورسانت سیستمی
    case BONUS = 'bonus';           // پاداش / هدیه

    public function label(): string
    {
        return match ($this) {
            self::DEPOSIT => 'واریز / شارژ',
            self::WITHDRAW => 'برداشت / تسویه',
            self::PAYMENT => 'پرداخت سفارش / خدمات',
            self::REFUND => 'بازگشت وجه',
            self::TRANSFER => 'انتقال موجودی',
            self::COMMISSION => 'پورسانت',
            self::BONUS => 'پاداش / هدیه اعتباری',
        };
    }
}
