<?php

namespace Modules\Properties\App\Http\Controllers\User;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Properties\Entities\PropertySetting;
use Modules\Properties\Entities\Property;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:properties.settings.manage')->only(['index', 'update']);
    }

    public function index()
    {
        $currency = PropertySetting::get('currency', 'toman');
        $max_file_size = PropertySetting::get('max_file_size', 10240);
        $max_gallery_images = PropertySetting::get('max_gallery_images', 10);
        $allowed_file_types = PropertySetting::get('allowed_file_types', 'jpeg,png,jpg,gif');

        // Video Settings
        $max_video_size = PropertySetting::get('max_video_size', 20480); // 20MB default
        $allowed_video_types = PropertySetting::get('allowed_video_types', 'mp4,mov,avi');

        // Code Settings
        $property_code_prefix = PropertySetting::get('property_code_prefix', 'P');
        $property_code_separator = PropertySetting::get('property_code_separator', '-');
        $property_code_include_year = PropertySetting::get('property_code_include_year', 1);
        $property_code_use_category_slug = PropertySetting::get('property_code_use_category_slug', 0);

        // Card Display Settings
        $show_features_in_card = PropertySetting::get('show_features_in_card', 1);
        $show_bookmark_button = PropertySetting::get('show_bookmark_button', 1);

        // Guest Restriction Settings
        $restrict_public_index_guests = PropertySetting::get('restrict_public_index_guests', 0);
        $restrict_public_map_guests = PropertySetting::get('restrict_public_map_guests', 0);

        // AI Settings
        $ai_property_completion = PropertySetting::get('ai_property_completion', 0);
        $ai_property_search = PropertySetting::get('ai_property_search', 0);

        // Visibility Settings
        $roles = Role::all();

        // Helper to get setting or default
        $getSetting = function($key, $default = []) {
            $val = PropertySetting::get($key);
            $decoded = $val ? json_decode($val, true) : [];
            // If decoded is empty (e.g. "[]" or null), use default.
            // This ensures that if the setting was saved as empty previously (or never set), defaults are applied.
            return !empty($decoded) ? $decoded : $default;
        };

        // For sensitive data, default to admin/super-admin if not set
        $visibility_owner_info = $getSetting('visibility_owner_info', ['super-admin', 'admin', 'guest']);
        $visibility_confidential_notes = $getSetting('visibility_confidential_notes', ['super-admin', 'admin', 'guest']);

        // For public data, default to empty (public)
        $visibility_price_info = $getSetting('visibility_price_info', ['super-admin', 'admin', 'guest']);
        $visibility_map_info = $getSetting('visibility_map_info', ['super-admin', 'admin', 'guest']);
        $visibility_cover_image = $getSetting('visibility_cover_image', ['super-admin', 'admin', 'guest']);
        $visibility_gallery_images = $getSetting('visibility_gallery_images', ['super-admin', 'admin', 'guest']);
        $visibility_min_price = $getSetting('visibility_min_price', ['super-admin', 'admin', 'guest']);
        $visibility_convertible = $getSetting('visibility_convertible', ['super-admin', 'admin', 'guest']);
        $visibility_convertible_with = $getSetting('visibility_convertible_with', ['super-admin', 'admin', 'guest']);

        // Agent Roles
        $agent_roles = $getSetting('agent_roles', []);

        // Office Location Settings
        $office_location_lat = PropertySetting::get('office_location_lat');
        $office_location_lng = PropertySetting::get('office_location_lng');
        $office_location_title = PropertySetting::get('office_location_title', 'دفتر مرکزی');

        // Map Service Settings
        $map_service = PropertySetting::get('map_service', 'leaflet');
        $map_ir_api_key = PropertySetting::get('map_ir_api_key');

        // Rental Mode & Multi-Host Settings
        $rental_mode_enabled = PropertySetting::get('rental_mode_enabled', 0);
        $rental_video_enabled = PropertySetting::get('rental_video_enabled', 1);
        $rental_building_enabled = PropertySetting::get('rental_building_enabled', 1);
        $rental_multi_host_enabled = PropertySetting::get('rental_multi_host_enabled', 0);
        $rental_host_auto_approve = PropertySetting::get('rental_host_auto_approve', 0);
        $rental_property_auto_approve = PropertySetting::get('rental_property_auto_approve', 0);
        $rental_pending_host_can_create = PropertySetting::get('rental_pending_host_can_create', 0);
        $rental_redirect_host_to_dashboard = PropertySetting::get('rental_redirect_host_to_dashboard', 1);
        $rental_default_commission = PropertySetting::get('rental_default_commission', 10);
        $rental_property_types = Property::getRentalPropertyTypes();

        // Settlement Rules Settings
        $rental_settlement_rules_title = PropertySetting::get('rental_settlement_rules_title', 'قوانین و رویه تسویه حساب درآمد:');
        $rawSettlementRules = PropertySetting::get('rental_settlement_rules');
        $rental_settlement_rules = $rawSettlementRules ? json_decode($rawSettlementRules, true) : null;
        if (empty($rental_settlement_rules) || !is_array($rental_settlement_rules)) {
            $rental_settlement_rules = [
                [
                    'title' => 'زمان تسویه',
                    'text' => 'مبالغ رزروها پس از تحویل اقامتگاه به مسافر و ورود بدون مغایرت در اولین سیکل پایا واریز می‌گردد.',
                ],
                [
                    'title' => 'کارمزد پلتفرم',
                    'text' => 'سهم پلتفرم از هر رزرو {commission}٪ بوده و مابقی مستقیماً به شبا واریز می‌شود.',
                ],
                [
                    'title' => 'تطابق حساب',
                    'text' => 'نام صاحب حساب باید با اطلاعات هویتی و کد ملی همخوانی کامل داشته باشد.',
                ],
            ];
        }

        // Rental Display & Host Privacy Settings (تنظیمات نمایش جزئیات اقامتگاه، نقشه و تماس)
        $rental_show_host_box = PropertySetting::get('rental_show_host_box', 'all');
        $rental_host_show_avatar = PropertySetting::get('rental_host_show_avatar', 1);
        $rental_host_show_badge = PropertySetting::get('rental_host_show_badge', 1);
        $rental_host_show_about = PropertySetting::get('rental_host_show_about', 1);
        $rental_host_show_phone = PropertySetting::get('rental_host_show_phone', 'confirmed_booking');

        $rental_show_address = PropertySetting::get('rental_show_address', 'all');
        $rental_show_map = PropertySetting::get('rental_show_map', 'all');
        $rental_map_display_type = PropertySetting::get('rental_map_display_type', 'circle_then_exact');
        $rental_map_circle_radius = PropertySetting::get('rental_map_circle_radius', 300);

        $rental_show_direct_call_btn = PropertySetting::get('rental_show_direct_call_btn', 'all');

        // Host Tips Settings (نکات کلیدی میزبانی موفق)
        $rental_host_tips_enabled = PropertySetting::get('rental_host_tips_enabled', 1);
        $rental_host_tips_title = PropertySetting::get('rental_host_tips_title', 'نکات کلیدی میزبانی موفق');
        $rawHostTips = PropertySetting::get('rental_host_tips');
        $rental_host_tips = $rawHostTips ? json_decode($rawHostTips, true) : null;
        if (empty($rental_host_tips) || !is_array($rental_host_tips)) {
            $rental_host_tips = [
                [
                    'title' => 'بروزرسانی تقویم',
                    'text' => 'روزهایی که اقامتگاه خارج از پلتفرم پر شده است را از بخش «مدیریت تقویم» مسدود نمایید تا از تداخل رزرو جلوگیری شود.',
                ],
                [
                    'title' => 'نرخ‌گذاری فصلی و پیک',
                    'text' => 'تعیین نرخ دقیق آخر هفته و ایام پیک باعث افزایش رتبه نمایش اقامتگاه شما در نتایج جستجو می‌شود.',
                ],
                [
                    'title' => 'ویرایش مدارک هویتی',
                    'text' => 'در صورت تغییر اطلاعات احراز هویت پس از تأیید، وضعیت مدارک مجدداً جهت بررسی امنیتی به حالت «در انتظار بررسی» تغییر می‌یابد.',
                ],
            ];
        }

        // Storage Report
        $storagePath = 'properties';
        $totalSize = 0;
        $fileCount = 0;

        if (Storage::disk('public')->exists($storagePath)) {
            $files = Storage::disk('public')->allFiles($storagePath);
            $fileCount = count($files);
            foreach ($files as $file) {
                $totalSize += Storage::disk('public')->size($file);
            }
        }

        $formattedSize = $this->formatBytes($totalSize);

        return view('properties::user.settings.index', compact(
            'currency',
            'max_file_size',
            'max_gallery_images',
            'allowed_file_types',
            'max_video_size',
            'allowed_video_types',
            'property_code_prefix',
            'property_code_separator',
            'property_code_include_year',
            'property_code_use_category_slug',
            'show_features_in_card',
            'show_bookmark_button',
            'restrict_public_index_guests',
            'restrict_public_map_guests',
            'ai_property_completion',
            'ai_property_search',
            'formattedSize',
            'fileCount',
            'roles',
            'visibility_owner_info',
            'visibility_confidential_notes',
            'visibility_price_info',
            'visibility_map_info',
            'visibility_cover_image',
            'visibility_gallery_images',
            'visibility_min_price',
            'visibility_convertible',
            'visibility_convertible_with',
            'agent_roles',
            'office_location_lat',
            'office_location_lng',
            'office_location_title',
            'map_service',
            'map_ir_api_key',
            'rental_mode_enabled',
            'rental_video_enabled',
            'rental_building_enabled',
            'rental_multi_host_enabled',
            'rental_host_auto_approve',
            'rental_property_auto_approve',
            'rental_pending_host_can_create',
            'rental_redirect_host_to_dashboard',
            'rental_default_commission',
            'rental_settlement_rules_title',
            'rental_settlement_rules',
            'rental_host_tips_enabled',
            'rental_host_tips_title',
            'rental_host_tips',
            'rental_property_types',
            'rental_show_host_box',
            'rental_host_show_avatar',
            'rental_host_show_badge',
            'rental_host_show_about',
            'rental_host_show_phone',
            'rental_show_address',
            'rental_show_map',
            'rental_map_display_type',
            'rental_map_circle_radius',
            'rental_show_direct_call_btn'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'currency' => 'required|in:toman,rial',
            'max_file_size' => 'required|integer|min:1024',
            'max_gallery_images' => 'required|integer|min:1|max:50',
            'allowed_file_types' => 'required|string',
            'max_video_size' => 'required|integer|min:1024',
            'allowed_video_types' => 'required|string',
            'property_code_prefix' => 'nullable|string|max:10',
            'property_code_separator' => 'nullable|string|max:5',
            'property_code_include_year' => 'nullable|boolean',
            'property_code_use_category_slug' => 'nullable|boolean',
            'show_features_in_card' => 'nullable|boolean',
            'show_bookmark_button' => 'nullable|boolean',
            'restrict_public_index_guests' => 'nullable|boolean',
            'restrict_public_map_guests' => 'nullable|boolean',
            'ai_property_completion' => 'nullable|boolean',
            'ai_property_search' => 'nullable|boolean',
            'visibility_owner_info' => 'nullable|array',
            'visibility_confidential_notes' => 'nullable|array',
            'visibility_price_info' => 'nullable|array',
            'visibility_map_info' => 'nullable|array',
            'visibility_cover_image' => 'nullable|array',
            'visibility_gallery_images' => 'nullable|array',
            'visibility_min_price' => 'nullable|array',
            'visibility_convertible' => 'nullable|array',
            'visibility_convertible_with' => 'nullable|array',
            'agent_roles' => 'nullable|array',
            'office_location_lat' => 'nullable|numeric',
            'office_location_lng' => 'nullable|numeric',
            'office_location_title' => 'nullable|string|max:255',
            'map_service' => 'required|in:leaflet,map_ir',
            'map_ir_api_key' => 'nullable|string',
            'rental_mode_enabled' => 'nullable|boolean',
            'rental_video_enabled' => 'nullable|boolean',
            'rental_building_enabled' => 'nullable|boolean',
            'rental_multi_host_enabled' => 'nullable|boolean',
            'rental_host_auto_approve' => 'nullable|boolean',
            'rental_property_auto_approve' => 'nullable|boolean',
            'rental_pending_host_can_create' => 'nullable|boolean',
            'rental_redirect_host_to_dashboard' => 'nullable|boolean',
            'rental_default_commission' => 'nullable|numeric|min:0|max:100',
            'rental_settlement_rules_title' => 'nullable|string|max:255',
            'rental_settlement_rules' => 'nullable|array',
            'rental_host_tips_enabled' => 'nullable|boolean',
            'rental_host_tips_title' => 'nullable|string|max:255',
            'rental_host_tips' => 'nullable|array',
            'rental_property_types' => 'nullable|array',
            'rental_show_host_box' => 'nullable|in:all,confirmed_booking,none',
            'rental_host_show_avatar' => 'nullable|boolean',
            'rental_host_show_badge' => 'nullable|boolean',
            'rental_host_show_about' => 'nullable|boolean',
            'rental_host_show_phone' => 'nullable|in:all,confirmed_booking,none',
            'rental_show_address' => 'nullable|in:all,confirmed_booking,none',
            'rental_show_map' => 'nullable|in:all,confirmed_booking,none',
            'rental_map_display_type' => 'nullable|in:circle,exact,circle_then_exact',
            'rental_map_circle_radius' => 'nullable|integer|min:50|max:5000',
            'rental_show_direct_call_btn' => 'nullable|in:all,confirmed_booking,none',
        ]);

        $allowedFileTypes = str_replace(' ', '', $request->allowed_file_types);
        $allowedVideoTypes = str_replace(' ', '', $request->allowed_video_types);

        PropertySetting::set('currency', $request->currency);
        PropertySetting::set('max_file_size', $request->max_file_size);
        PropertySetting::set('max_gallery_images', $request->max_gallery_images);
        PropertySetting::set('allowed_file_types', $allowedFileTypes);

        PropertySetting::set('max_video_size', $request->max_video_size);
        PropertySetting::set('allowed_video_types', $allowedVideoTypes);

        PropertySetting::set('property_code_prefix', $request->property_code_prefix);
        PropertySetting::set('property_code_separator', $request->property_code_separator);
        PropertySetting::set('property_code_include_year', $request->has('property_code_include_year') ? 1 : 0);
        PropertySetting::set('property_code_use_category_slug', $request->has('property_code_use_category_slug') ? 1 : 0);

        PropertySetting::set('show_features_in_card', $request->has('show_features_in_card') ? 1 : 0);
        PropertySetting::set('show_bookmark_button', $request->has('show_bookmark_button') ? 1 : 0);
        PropertySetting::set('restrict_public_index_guests', $request->has('restrict_public_index_guests') ? 1 : 0);
        PropertySetting::set('restrict_public_map_guests', $request->has('restrict_public_map_guests') ? 1 : 0);

        // Save AI Settings
        PropertySetting::set('ai_property_completion', $request->has('ai_property_completion') ? 1 : 0);
        PropertySetting::set('ai_property_search', $request->has('ai_property_search') ? 1 : 0);

        // Save Visibility Settings
        PropertySetting::set('visibility_owner_info', json_encode($request->input('visibility_owner_info', [])));
        PropertySetting::set('visibility_confidential_notes', json_encode($request->input('visibility_confidential_notes', [])));
        PropertySetting::set('visibility_price_info', json_encode($request->input('visibility_price_info', [])));
        PropertySetting::set('visibility_map_info', json_encode($request->input('visibility_map_info', [])));
        PropertySetting::set('visibility_cover_image', json_encode($request->input('visibility_cover_image', [])));
        PropertySetting::set('visibility_gallery_images', json_encode($request->input('visibility_gallery_images', [])));
        PropertySetting::set('visibility_min_price', json_encode($request->input('visibility_min_price', [])));
        PropertySetting::set('visibility_convertible', json_encode($request->input('visibility_convertible', [])));
        PropertySetting::set('visibility_convertible_with', json_encode($request->input('visibility_convertible_with', [])));

        // Save Agent Roles
        PropertySetting::set('agent_roles', json_encode($request->input('agent_roles', [])));

        // Save Office Location
        PropertySetting::set('office_location_lat', $request->office_location_lat);
        PropertySetting::set('office_location_lng', $request->office_location_lng);
        PropertySetting::set('office_location_title', $request->office_location_title);

        // Save Map Service Settings
        PropertySetting::set('map_service', $request->map_service);
        PropertySetting::set('map_ir_api_key', $request->map_ir_api_key);

        // Save Rental & Multi-Host Settings
        PropertySetting::set('rental_mode_enabled', $request->has('rental_mode_enabled') ? 1 : 0);
        PropertySetting::set('rental_video_enabled', $request->has('rental_video_enabled') ? 1 : 0);
        PropertySetting::set('rental_building_enabled', $request->has('rental_building_enabled') ? 1 : 0);
        PropertySetting::set('rental_multi_host_enabled', $request->has('rental_multi_host_enabled') ? 1 : 0);
        PropertySetting::set('rental_host_auto_approve', $request->has('rental_host_auto_approve') ? 1 : 0);
        PropertySetting::set('rental_property_auto_approve', $request->has('rental_property_auto_approve') ? 1 : 0);
        PropertySetting::set('rental_pending_host_can_create', $request->has('rental_pending_host_can_create') ? 1 : 0);
        PropertySetting::set('rental_redirect_host_to_dashboard', $request->has('rental_redirect_host_to_dashboard') ? 1 : 0);
        PropertySetting::set('rental_default_commission', $request->input('rental_default_commission', 10));

        // Save Settlement Rules Settings
        if ($request->has('rental_settlement_rules_title')) {
            PropertySetting::set('rental_settlement_rules_title', $request->input('rental_settlement_rules_title', 'قوانین و رویه تسویه حساب درآمد:'));
        }
        if ($request->has('rental_settlement_rules') && is_array($request->rental_settlement_rules)) {
            $cleanRules = [];
            foreach ($request->rental_settlement_rules as $r) {
                if (!empty($r['text']) || !empty($r['title'])) {
                    $cleanRules[] = [
                        'title' => trim($r['title'] ?? ''),
                        'text' => trim($r['text'] ?? ''),
                    ];
                }
            }
            PropertySetting::set('rental_settlement_rules', json_encode($cleanRules, JSON_UNESCAPED_UNICODE));
        }

        // Save Host Tips Settings
        PropertySetting::set('rental_host_tips_enabled', $request->has('rental_host_tips_enabled') ? 1 : 0);
        if ($request->has('rental_host_tips_title')) {
            PropertySetting::set('rental_host_tips_title', $request->input('rental_host_tips_title', 'نکات کلیدی میزبانی موفق'));
        }
        if ($request->has('rental_host_tips') && is_array($request->rental_host_tips)) {
            $cleanTips = [];
            foreach ($request->rental_host_tips as $t) {
                if (!empty($t['text']) || !empty($t['title'])) {
                    $cleanTips[] = [
                        'title' => trim($t['title'] ?? ''),
                        'text' => trim($t['text'] ?? ''),
                    ];
                }
            }
            PropertySetting::set('rental_host_tips', json_encode($cleanTips, JSON_UNESCAPED_UNICODE));
        }

        // Save Rental Property Types Settings
        if ($request->has('rental_property_types') && is_array($request->rental_property_types)) {
            $cleanRentalTypes = [];
            foreach ($request->rental_property_types as $key => $item) {
                $typeKey = trim($item['key'] ?? $key);
                $typeLabel = trim($item['label'] ?? '');
                $isActive = !empty($item['is_active']);

                if (!empty($typeKey) && !empty($typeLabel)) {
                    $cleanRentalTypes[$typeKey] = [
                        'key' => $typeKey,
                        'label' => $typeLabel,
                        'is_active' => $isActive,
                    ];
                }
            }
            if (!empty($cleanRentalTypes)) {
                PropertySetting::set('rental_property_types', json_encode($cleanRentalTypes, JSON_UNESCAPED_UNICODE));
            }
        }

        // Save Rental Display & Privacy Settings
        PropertySetting::set('rental_show_host_box', $request->input('rental_show_host_box', 'all'));
        PropertySetting::set('rental_host_show_avatar', $request->has('rental_host_show_avatar') ? 1 : 0);
        PropertySetting::set('rental_host_show_badge', $request->has('rental_host_show_badge') ? 1 : 0);
        PropertySetting::set('rental_host_show_about', $request->has('rental_host_show_about') ? 1 : 0);
        PropertySetting::set('rental_host_show_phone', $request->input('rental_host_show_phone', 'confirmed_booking'));

        PropertySetting::set('rental_show_address', $request->input('rental_show_address', 'all'));
        PropertySetting::set('rental_show_map', $request->input('rental_show_map', 'all'));
        PropertySetting::set('rental_map_display_type', $request->input('rental_map_display_type', 'circle_then_exact'));
        PropertySetting::set('rental_map_circle_radius', (int) $request->input('rental_map_circle_radius', 300));

        PropertySetting::set('rental_show_direct_call_btn', $request->input('rental_show_direct_call_btn', 'all'));

        return back()->with('success', 'تنظیمات با موفقیت ذخیره شد.');
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
