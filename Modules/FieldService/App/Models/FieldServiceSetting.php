<?php

namespace Modules\FieldService\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FieldServiceSetting extends Model
{
    use HasFactory;

    protected $table = 'field_service_settings';

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if (!$setting || $setting->value === null) {
            return $default;
        }

        $val = $setting->value;
        $decoded = json_decode($val, true);
        if (json_last_error() === JSON_ERROR_NONE && (is_array($decoded) || is_object($decoded))) {
            return $decoded;
        }

        return $val;
    }

    public static function set(string $key, mixed $value, string $group = 'general'): self
    {
        $storeValue = is_array($value) || is_object($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value;

        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $storeValue,
                'group' => $group,
            ]
        );
    }

    /**
     * Get platform name dynamically from general settings (/settings#general > app_name).
     */
    public static function getPlatformName(): string
    {
        if (function_exists('get_setting')) {
            $val = get_setting('app_name');
            if (!empty($val)) {
                return (string) $val;
            }
        }

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $val = \Modules\Settings\Entities\Setting::where('key', 'app_name')->value('value');
                if (!empty($val)) {
                    return (string) $val;
                }
            }
        } catch (\Throwable $e) {}

        return (string) config('app.name', 'آنتی‌گرویتی');
    }

    /**
     * Get covered provinces (defaults to Mazandaran).
     */
    public static function getCoveredProvinces(): array
    {
        $val = static::get('covered_provinces', ['مازندران']);
        return is_array($val) && !empty($val) ? $val : ['مازندران'];
    }

    /**
     * Get covered cities.
     */
    public static function getCoveredCities(): array
    {
        $val = static::get('covered_cities', ['بابل', 'ساری', 'آمل', 'قائمشهر', 'بابلسر', 'محمودآباد']);
        return is_array($val) && !empty($val) ? $val : ['بابل', 'ساری', 'آمل', 'قائمشهر', 'بابلسر', 'محمودآباد'];
    }

    /**
     * Get covered cities with coordinate metadata.
     */
    public static function getCoveredCitiesWithCoords(): array
    {
        $cities = static::getCoveredCities();
        $dict = static::getKnownCityCoordinates();
        $result = [];

        foreach ($cities as $cityName) {
            $clean = trim($cityName);
            if (isset($dict[$clean])) {
                $result[$clean] = $dict[$clean];
            } else {
                $result[$clean] = [
                    'lat'  => 36.5444,
                    'lng'  => 52.6780,
                    'name' => $clean,
                ];
            }
        }

        return $result;
    }

    /**
     * Compute strict geographic bounding box and centroid enclosing active covered cities.
     */
    public static function getCoveredBoundingBox(): array
    {
        $cities = static::getCoveredCitiesWithCoords();

        if (empty($cities)) {
            return [
                'southWest' => [36.35, 52.45],
                'northEast' => [36.75, 52.95],
                'center'    => ['lat' => 36.5444, 'lng' => 52.6780],
                'min_zoom'  => 11,
                'zoom'      => 13,
            ];
        }

        $lats = array_column($cities, 'lat');
        $lngs = array_column($cities, 'lng');

        $minLat = min($lats);
        $maxLat = max($lats);
        $minLng = min($lngs);
        $maxLng = max($lngs);

        // Padding around the selected covered cities (~12-15 km margin)
        $paddingLat = 0.12;
        $paddingLng = 0.15;

        $southWest = [
            round(max(25.0, $minLat - $paddingLat), 4),
            round(max(44.0, $minLng - $paddingLng), 4),
        ];
        $northEast = [
            round(min(39.8, $maxLat + $paddingLat), 4),
            round(min(63.5, $maxLng + $paddingLng), 4),
        ];

        // For default zoom: Snapp & Tapsi standard street & alley detail level is 16
        $defaultZoom = 16;
        $minZoom = 9;

        // Hub center: prefer Babol if active, otherwise center of covered cities
        $centerLat = 36.5444;
        $centerLng = 52.6780;
        if (!isset($cities['بابل'])) {
            $centerLat = round(($minLat + $maxLat) / 2, 4);
            $centerLng = round(($minLng + $maxLng) / 2, 4);
        }

        return [
            'southWest' => $southWest,
            'northEast' => $northEast,
            'center'    => ['lat' => $centerLat, 'lng' => $centerLng],
            'min_zoom'  => $minZoom,
            'zoom'      => $defaultZoom,
        ];
    }

    /**
     * Built-in dictionary of Iranian city coordinates (focused on Mazandaran, Tehran, and major hubs).
     */
    public static function getKnownCityCoordinates(): array
    {
        return [
            // Mazandaran
            'بابل'       => ['lat' => 36.5444, 'lng' => 52.6780, 'name' => 'بابل (مرکز عملیات)'],
            'ساری'       => ['lat' => 36.5659, 'lng' => 53.0586, 'name' => 'ساری'],
            'آمل'        => ['lat' => 36.4676, 'lng' => 52.3507, 'name' => 'آمل'],
            'قائمشهر'    => ['lat' => 36.4646, 'lng' => 52.8596, 'name' => 'قائمشهر'],
            'بابلسر'     => ['lat' => 36.7024, 'lng' => 52.6575, 'name' => 'بابلسر'],
            'محمودآباد'  => ['lat' => 36.6319, 'lng' => 52.2629, 'name' => 'محمودآباد'],
            'نور'        => ['lat' => 36.5728, 'lng' => 52.0150, 'name' => 'نور'],
            'نوشهر'      => ['lat' => 36.6492, 'lng' => 51.4964, 'name' => 'نوشهر'],
            'چالوس'      => ['lat' => 36.6550, 'lng' => 51.4204, 'name' => 'چالوس'],
            'تنکابن'     => ['lat' => 36.8163, 'lng' => 50.8738, 'name' => 'تنکابن'],
            'رامسر'      => ['lat' => 36.9175, 'lng' => 50.6483, 'name' => 'رامسر'],
            'بهشهر'      => ['lat' => 36.6989, 'lng' => 53.5517, 'name' => 'بهشهر'],
            'نکا'        => ['lat' => 36.6508, 'lng' => 53.2990, 'name' => 'نکا'],
            'جویبار'     => ['lat' => 36.6410, 'lng' => 52.9122, 'name' => 'جویبار'],
            'فریدونکنار' => ['lat' => 36.6864, 'lng' => 52.5186, 'name' => 'فریدونکنار'],
            'سوادکوه'    => ['lat' => 36.0833, 'lng' => 52.9500, 'name' => 'سوادکوه'],
            'گلوگاه'     => ['lat' => 36.7275, 'lng' => 53.8097, 'name' => 'گلوگاه'],
            'کلاردشت'    => ['lat' => 36.4886, 'lng' => 51.1542, 'name' => 'کلاردشت'],
            'عباس‌آباد'  => ['lat' => 36.7247, 'lng' => 51.1092, 'name' => 'عباس‌آباد'],

            // Tehran & Alborz
            'تهران'      => ['lat' => 35.6892, 'lng' => 51.3890, 'name' => 'تهران'],
            'کرج'        => ['lat' => 35.8327, 'lng' => 50.9915, 'name' => 'کرج'],
            'ری'         => ['lat' => 35.5947, 'lng' => 51.4356, 'name' => 'ری'],
            'شهریار'     => ['lat' => 35.6597, 'lng' => 51.0583, 'name' => 'شهریار'],
            'اسلامشهر'   => ['lat' => 35.5422, 'lng' => 51.2308, 'name' => 'اسلامشهر'],
            'پردیس'      => ['lat' => 35.7383, 'lng' => 51.7892, 'name' => 'پردیس'],

            // Gilan & Golestan
            'رشت'        => ['lat' => 37.2809, 'lng' => 49.5924, 'name' => 'رشت'],
            'انزلی'      => ['lat' => 37.4728, 'lng' => 49.4622, 'name' => 'بندر انزلی'],
            'لاهیجان'    => ['lat' => 37.2072, 'lng' => 50.0031, 'name' => 'لاهیجان'],
            'گرگان'      => ['lat' => 36.8427, 'lng' => 54.4439, 'name' => 'گرگان'],
            'گنبد کاووس' => ['lat' => 37.2500, 'lng' => 55.1672, 'name' => 'گنبد کاووس'],

            // Major National Metropolises
            'مشهد'       => ['lat' => 36.2972, 'lng' => 59.6067, 'name' => 'مشهد'],
            'اصفهان'     => ['lat' => 32.6546, 'lng' => 51.6680, 'name' => 'اصفهان'],
            'شیراز'      => ['lat' => 29.5918, 'lng' => 52.5837, 'name' => 'شیراز'],
            'تبریز'      => ['lat' => 38.0800, 'lng' => 46.2919, 'name' => 'تبریز'],
            'قم'         => ['lat' => 34.6401, 'lng' => 50.8764, 'name' => 'قم'],
            'اهواز'      => ['lat' => 31.3183, 'lng' => 48.6706, 'name' => 'اهواز'],
        ];
    }
}
