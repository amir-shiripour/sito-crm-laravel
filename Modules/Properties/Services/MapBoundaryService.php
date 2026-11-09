<?php

namespace Modules\Properties\Services;

use Modules\Properties\Entities\PropertySetting;

class MapBoundaryService
{
    /**
     * مختصات کل کشور ایران
     * جنوب غربی: [25.0, 44.0]
     * شمال شرقی: [39.8, 63.5]
     */
    public const IRAN_BOUNDS = [
        [25.0, 44.0],
        [39.8, 63.5]
    ];

    public const IRAN_CENTER = [32.4279, 53.6880];
    public const IRAN_DEFAULT_ZOOM = 6;
    public const IRAN_MIN_ZOOM = 5;

    /**
     * مختصات مستطیل دربرگیرنده (Bounding Box) و مرکز ۳۱ استان ایران
     * [minLat, minLng], [maxLat, maxLng]
     */
    public static array $provinces = [
        'مازندران' => [
            'bounds' => [[35.75, 50.30], [36.98, 54.10]],
            'center' => [36.35, 52.30],
            'zoom' => 8,
            'minZoom' => 7,
        ],
        'گیلان' => [
            'bounds' => [[36.55, 48.55], [38.48, 50.60]],
            'center' => [37.28, 49.60],
            'zoom' => 8,
            'minZoom' => 7,
        ],
        'گلستان' => [
            'bounds' => [[36.50, 53.85], [38.15, 56.35]],
            'center' => [37.25, 55.15],
            'zoom' => 8,
            'minZoom' => 7,
        ],
        'تهران' => [
            'bounds' => [[35.15, 50.95], [36.05, 53.15]],
            'center' => [35.69, 51.39],
            'zoom' => 9,
            'minZoom' => 8,
        ],
        'البرز' => [
            'bounds' => [[35.55, 50.25], [36.35, 51.50]],
            'center' => [35.83, 50.98],
            'zoom' => 9,
            'minZoom' => 8,
        ],
        'قم' => [
            'bounds' => [[34.15, 50.10], [35.20, 51.95]],
            'center' => [34.64, 50.88],
            'zoom' => 9,
            'minZoom' => 8,
        ],
        'قزوین' => [
            'bounds' => [[35.40, 49.05], [36.80, 50.85]],
            'center' => [36.27, 50.00],
            'zoom' => 8,
            'minZoom' => 7,
        ],
        'زنجان' => [
            'bounds' => [[35.55, 47.15], [37.15, 49.45]],
            'center' => [36.67, 48.48],
            'zoom' => 8,
            'minZoom' => 7,
        ],
        'سمنان' => [
            'bounds' => [[34.10, 51.90], [37.45, 56.45]],
            'center' => [35.58, 53.39],
            'zoom' => 7,
            'minZoom' => 6,
        ],
        'اصفهان' => [
            'bounds' => [[30.70, 49.60], [34.45, 55.50]],
            'center' => [32.65, 51.67],
            'zoom' => 7,
            'minZoom' => 6,
        ],
        'فارس' => [
            'bounds' => [[27.05, 50.50], [31.75, 55.60]],
            'center' => [29.60, 52.55],
            'zoom' => 7,
            'minZoom' => 6,
        ],
        'خراسان رضوی' => [
            'bounds' => [[33.85, 56.30], [37.75, 61.25]],
            'center' => [36.30, 59.60],
            'zoom' => 7,
            'minZoom' => 6,
        ],
        'خراسان شمالی' => [
            'bounds' => [[36.60, 55.90], [38.30, 58.40]],
            'center' => [37.47, 57.33],
            'zoom' => 8,
            'minZoom' => 7,
        ],
        'خراسان جنوبی' => [
            'bounds' => [[30.50, 57.10], [34.35, 60.95]],
            'center' => [32.86, 59.22],
            'zoom' => 7,
            'minZoom' => 6,
        ],
        'آذربایجان شرقی' => [
            'bounds' => [[36.75, 45.10], [39.45, 47.85]],
            'center' => [38.08, 46.29],
            'zoom' => 7,
            'minZoom' => 6,
        ],
        'آذربایجان غربی' => [
            'bounds' => [[35.95, 44.05], [39.78, 45.65]],
            'center' => [37.55, 45.07],
            'zoom' => 7,
            'minZoom' => 6,
        ],
        'اردبیل' => [
            'bounds' => [[37.10, 47.25], [39.70, 48.95]],
            'center' => [38.25, 48.30],
            'zoom' => 8,
            'minZoom' => 7,
        ],
        'کردستان' => [
            'bounds' => [[34.75, 45.50], [36.50, 48.35]],
            'center' => [35.31, 46.99],
            'zoom' => 8,
            'minZoom' => 7,
        ],
        'کرمانشاه' => [
            'bounds' => [[33.60, 45.35], [35.30, 48.10]],
            'center' => [34.33, 47.08],
            'zoom' => 8,
            'minZoom' => 7,
        ],
        'ایلام' => [
            'bounds' => [[31.95, 45.65], [34.05, 47.75]],
            'center' => [33.64, 46.42],
            'zoom' => 8,
            'minZoom' => 7,
        ],
        'لرستان' => [
            'bounds' => [[32.60, 46.85], [34.40, 50.10]],
            'center' => [33.48, 48.35],
            'zoom' => 8,
            'minZoom' => 7,
        ],
        'خوزستان' => [
            'bounds' => [[29.95, 47.65], [33.05, 50.65]],
            'center' => [31.32, 48.67],
            'zoom' => 7,
            'minZoom' => 6,
        ],
        'همدان' => [
            'bounds' => [[33.95, 47.75], [35.60, 49.35]],
            'center' => [34.80, 48.51],
            'zoom' => 8,
            'minZoom' => 7,
        ],
        'مرکزی' => [
            'bounds' => [[33.50, 48.95], [35.55, 51.10]],
            'center' => [34.09, 49.69],
            'zoom' => 8,
            'minZoom' => 7,
        ],
        'چهارمحال و بختیاری' => [
            'bounds' => [[31.15, 49.85], [32.85, 51.45]],
            'center' => [32.33, 50.86],
            'zoom' => 8,
            'minZoom' => 7,
        ],
        'کهگیلویه و بویراحمد' => [
            'bounds' => [[30.15, 49.90], [31.55, 51.70]],
            'center' => [30.67, 51.59],
            'zoom' => 8,
            'minZoom' => 7,
        ],
        'بوشهر' => [
            'bounds' => [[27.25, 50.10], [30.30, 52.95]],
            'center' => [28.92, 50.84],
            'zoom' => 7,
            'minZoom' => 6,
        ],
        'هرمزگان' => [
            'bounds' => [[25.40, 52.65], [28.95, 59.25]],
            'center' => [27.18, 56.27],
            'zoom' => 7,
            'minZoom' => 6,
        ],
        'کرمان' => [
            'bounds' => [[26.85, 54.35], [31.85, 59.45]],
            'center' => [30.28, 57.08],
            'zoom' => 7,
            'minZoom' => 6,
        ],
        'یزد' => [
            'bounds' => [[29.85, 52.80], [35.15, 57.75]],
            'center' => [31.89, 54.37],
            'zoom' => 7,
            'minZoom' => 6,
        ],
        'سیستان و بلوچستان' => [
            'bounds' => [[25.05, 58.90], [31.50, 63.35]],
            'center' => [29.49, 60.86],
            'zoom' => 6,
            'minZoom' => 5,
        ],
    ];

    /**
     * دریافت لیست نام تمام استان‌های تعریف‌شده
     */
    public static function getProvinceNames(): array
    {
        return array_keys(self::$provinces);
    }

    /**
     * دریافت پیکربندی نهایی باند نقشه بر اساس تنظیمات پلتفرم
     */
    public static function getBoundsConfig(): array
    {
        $scope = PropertySetting::get('map_boundary_scope', 'iran');
        $rawSelected = PropertySetting::get('map_selected_provinces');
        
        $selectedProvinces = [];
        if (!empty($rawSelected)) {
            $decoded = is_array($rawSelected) ? $rawSelected : json_decode($rawSelected, true);
            if (is_array($decoded)) {
                $selectedProvinces = array_values(array_filter($decoded));
            }
        }

        // اگر حالت استانی انتخاب شده بود و حداقل یک استان معتبر وجود داشت:
        if ($scope === 'custom_provinces' && !empty($selectedProvinces)) {
            $validProvinces = array_intersect($selectedProvinces, array_keys(self::$provinces));
            
            if (!empty($validProvinces)) {
                $minLat = 90.0;
                $maxLat = -90.0;
                $minLng = 180.0;
                $maxLng = -180.0;

                foreach ($validProvinces as $provName) {
                    $b = self::$provinces[$provName]['bounds'];
                    $minLat = min($minLat, $b[0][0]);
                    $minLng = min($minLng, $b[0][1]);
                    $maxLat = max($maxLat, $b[1][0]);
                    $maxLng = max($maxLng, $b[1][1]);
                }

                // اضافه کردن کمی حاشیه امن (Padding: 0.05 درجه) برای آزادی عمل کاربر در مرزها
                $minLat = max(24.0, round($minLat - 0.05, 4));
                $minLng = max(43.5, round($minLng - 0.05, 4));
                $maxLat = min(40.0, round($maxLat + 0.05, 4));
                $maxLng = min(64.0, round($maxLng + 0.05, 4));

                $centerLat = round(($minLat + $maxLat) / 2, 4);
                $centerLng = round(($minLng + $maxLng) / 2, 4);

                $count = count($validProvinces);
                $zoom = ($count === 1) ? (self::$provinces[reset($validProvinces)]['zoom'] ?? 8) : 7;
                $minZoom = ($count === 1) ? (self::$provinces[reset($validProvinces)]['minZoom'] ?? 7) : 6;

                return [
                    'scope' => 'custom_provinces',
                    'provinces' => array_values($validProvinces),
                    'bounds' => [
                        [$minLat, $minLng],
                        [$maxLat, $maxLng]
                    ],
                    'center' => [$centerLat, $centerLng],
                    'zoom' => $zoom,
                    'minZoom' => $minZoom,
                    'maxBoundsViscosity' => 1.0,
                ];
            }
        }

        // پیش‌فرض قطعی سامانه: سراسر کشور ایران
        return [
            'scope' => 'iran',
            'provinces' => [],
            'bounds' => self::IRAN_BOUNDS,
            'center' => self::IRAN_CENTER,
            'zoom' => self::IRAN_DEFAULT_ZOOM,
            'minZoom' => self::IRAN_MIN_ZOOM,
            'maxBoundsViscosity' => 1.0,
        ];
    }
}
