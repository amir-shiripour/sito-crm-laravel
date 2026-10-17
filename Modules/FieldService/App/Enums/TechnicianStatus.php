<?php

namespace Modules\FieldService\App\Enums;

enum TechnicianStatus: string
{
    case PENDING_APPROVAL = 'pending_approval';
    case ACTIVE           = 'active';
    case SUSPENDED        = 'suspended';
    case INACTIVE         = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::PENDING_APPROVAL => 'در انتظار تایید مدارک',
            self::ACTIVE           => 'فعال و تایید شده',
            self::SUSPENDED        => 'تعلیق شده',
            self::INACTIVE         => 'غیرفعال',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING_APPROVAL => 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300',
            self::ACTIVE           => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300',
            self::SUSPENDED        => 'bg-rose-100 text-rose-800 dark:bg-rose-500/15 dark:text-rose-300',
            self::INACTIVE         => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
        };
    }
}
