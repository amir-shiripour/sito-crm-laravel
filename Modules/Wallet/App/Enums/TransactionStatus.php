<?php

namespace Modules\Wallet\App\Enums;

enum TransactionStatus: string
{
    case PENDING = 'pending';       // در انتظار
    case COMPLETED = 'completed';   // تکمیل شده
    case FAILED = 'failed';         // ناموفق
    case CANCELLED = 'cancelled';   // لغو شده

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'در انتظار',
            self::COMPLETED => 'موفق',
            self::FAILED => 'ناموفق',
            self::CANCELLED => 'لغو شده',
        };
    }
}
