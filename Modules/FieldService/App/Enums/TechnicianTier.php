<?php

namespace Modules\FieldService\App\Enums;

enum TechnicianTier: string
{
    case SILVER = 'silver';
    case GOLD   = 'gold';
    case MASTER = 'master';

    public function label(): string
    {
        return match ($this) {
            self::SILVER => 'تکنسین نقره‌ای',
            self::GOLD   => 'تکنسین طلایی (VIP)',
            self::MASTER => 'استادکار ارشد (Master)',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::SILVER => 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600',
            self::GOLD   => 'bg-amber-100 text-amber-900 dark:bg-amber-500/20 dark:text-amber-300 border border-amber-300 dark:border-amber-500/40',
            self::MASTER => 'bg-indigo-100 text-indigo-900 dark:bg-indigo-500/20 dark:text-indigo-300 border border-indigo-300 dark:border-indigo-500/40',
        };
    }
}
