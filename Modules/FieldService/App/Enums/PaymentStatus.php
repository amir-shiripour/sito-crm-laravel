<?php

namespace Modules\FieldService\App\Enums;

enum PaymentStatus: string
{
    case PENDING     = 'pending';
    case ESCROW_HELD = 'escrow_held';
    case RELEASED    = 'released';
    case REFUNDED    = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING     => 'در انتظار پرداخت مشتری',
            self::ESCROW_HELD => 'امانی در حساب پلتفرم (Escrow)',
            self::RELEASED    => 'آزاد شده به تکنسین',
            self::REFUNDED    => 'عودت به مشتری',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING     => 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300',
            self::ESCROW_HELD => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-500/15 dark:text-indigo-300',
            self::RELEASED    => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300',
            self::REFUNDED    => 'bg-rose-100 text-rose-800 dark:bg-rose-500/15 dark:text-rose-300',
        };
    }
}
