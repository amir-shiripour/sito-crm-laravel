<?php

namespace Modules\FieldService\App\Enums;

enum ApplianceType: string
{
    case PACKAGE          = 'package';
    case AIR_CONDITIONER  = 'air_conditioner';
    case REFRIGERATOR     = 'refrigerator';
    case WASHING_MACHINE  = 'washing_machine';
    case CHILLER          = 'chiller';
    case BOILER           = 'boiler';
    case BOOSTER_PUMP     = 'booster_pump';
    case ELEVATOR         = 'elevator';
    case ELECTRICAL       = 'electrical';
    case PLUMBING         = 'plumbing';
    case OTHER            = 'other';

    public function label(): string
    {
        return match ($this) {
            self::PACKAGE          => 'پکیج و رادیاتور',
            self::AIR_CONDITIONER  => 'اسپلیت و کولر گازی',
            self::REFRIGERATOR     => 'یخچال و فریزر',
            self::WASHING_MACHINE  => 'ماشین لباسشویی و ظرفشویی',
            self::CHILLER          => 'چیلر و تهویه مطبوع صنعتی',
            self::BOILER           => 'موتورخانه مرکزی و مشعل',
            self::BOOSTER_PUMP     => 'بوستر پمپ و آبرسانی',
            self::ELEVATOR         => 'آسانسور و بالابر',
            self::ELECTRICAL       => 'برق و روشنایی ساختمان',
            self::PLUMBING         => 'تاسیسات لوله‌کشی و فاضلاب',
            self::OTHER            => 'سایر خدمات فنی',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PACKAGE          => '🔥',
            self::AIR_CONDITIONER  => '❄️',
            self::REFRIGERATOR     => '🧊',
            self::WASHING_MACHINE  => '🫧',
            self::CHILLER          => '🏭',
            self::BOILER           => '🛢️',
            self::BOOSTER_PUMP     => '💧',
            self::ELEVATOR         => '🛗',
            self::ELECTRICAL       => '⚡',
            self::PLUMBING         => '🔧',
            self::OTHER            => '🛠️',
        };
    }
}
