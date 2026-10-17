<?php

namespace Modules\FieldService\App\Enums;

enum OrderStatus: string
{
    case PENDING          = 'pending';
    case SEARCHING        = 'searching';
    case DISPATCHED       = 'dispatched';
    case ACCEPTED         = 'accepted';
    case EN_ROUTE         = 'en_route';
    case ARRIVED          = 'arrived';
    case IN_PROGRESS      = 'in_progress';
    case INVOICE_PENDING  = 'invoice_pending';
    case INVOICE_APPROVED = 'invoice_approved';
    case COMPLETED        = 'completed';
    case CANCELLED        = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING          => 'در انتظار بررسی',
            self::SEARCHING        => 'رادار جستجوی تکنسین',
            self::DISPATCHED       => 'پیشنهاد کار به تکنسین',
            self::ACCEPTED         => 'تکنسین تایید کرد',
            self::EN_ROUTE         => 'در مسیر به سمت مشتری',
            self::ARRIVED          => 'حضور در محل مشتری',
            self::IN_PROGRESS      => 'در حال عیب‌یابی و تعمیر',
            self::INVOICE_PENDING  => 'صدور فاکتور / در انتظار تایید',
            self::INVOICE_APPROVED => 'فاکتور تایید شد / ودیعه امانی',
            self::COMPLETED        => 'پایان موفق و ثبت گارانتی',
            self::CANCELLED        => 'لغو شده',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING          => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
            self::SEARCHING        => 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300 animate-pulse',
            self::DISPATCHED       => 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300',
            self::ACCEPTED         => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-500/15 dark:text-indigo-300',
            self::EN_ROUTE         => 'bg-sky-100 text-sky-800 dark:bg-sky-500/15 dark:text-sky-300',
            self::ARRIVED          => 'bg-teal-100 text-teal-800 dark:bg-teal-500/15 dark:text-teal-300',
            self::IN_PROGRESS      => 'bg-blue-100 text-blue-800 dark:bg-blue-500/15 dark:text-blue-300',
            self::INVOICE_PENDING  => 'bg-purple-100 text-purple-800 dark:bg-purple-500/15 dark:text-purple-300',
            self::INVOICE_APPROVED => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300',
            self::COMPLETED        => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300',
            self::CANCELLED        => 'bg-rose-100 text-rose-800 dark:bg-rose-500/15 dark:text-rose-300',
        };
    }
}
