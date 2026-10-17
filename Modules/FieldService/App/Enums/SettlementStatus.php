<?php

namespace Modules\FieldService\App\Enums;

enum SettlementStatus: string
{
    case PENDING    = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED  = 'completed';
    case REJECTED   = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING    => 'در صف تسویه پایا',
            self::PROCESSING => 'در حال ارسال به بانک',
            self::COMPLETED  => 'تسویه شده با حواله پایا',
            self::REJECTED   => 'رد شده',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING    => 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300',
            self::PROCESSING => 'bg-sky-100 text-sky-800 dark:bg-sky-500/15 dark:text-sky-300',
            self::COMPLETED  => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300',
            self::REJECTED   => 'bg-rose-100 text-rose-800 dark:bg-rose-500/15 dark:text-rose-300',
        };
    }
}
