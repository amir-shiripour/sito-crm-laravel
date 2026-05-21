<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Modules\Settings\Entities\Setting;

class ThemeComposer
{
    /**
     * دیکشنری رنگ‌های Tailwind و کد Hex آن‌ها برای المان‌های داینامیک
     */
    private $colorHexMap = [
        'indigo' => '#4f46e5', 'blue' => '#2563eb', 'sky' => '#0ea5e9', 'cyan' => '#06b6d4',
        'teal' => '#0d9488', 'emerald' => '#10b981', 'green' => '#16a34a', 'lime' => '#65a30d',
        'orange' => '#ea580c', 'amber' => '#d97706', 'red' => '#dc2626', 'rose' => '#e11d48',
        'purple' => '#9333ea', 'fuchsia' => '#c026d3', 'pink' => '#db2777', 'slate' => '#475569'
    ];

    /**
     * رنگ ثانویه برای ساخت گرادیانت‌های زیبا
     */
    private function getSecondaryColor($primary)
    {
        $map = [
            'indigo' => 'purple', 'blue' => 'cyan', 'sky' => 'blue', 'cyan' => 'teal',
            'teal' => 'emerald', 'emerald' => 'green', 'green' => 'lime', 'lime' => 'emerald',
            'orange' => 'red', 'amber' => 'orange', 'red' => 'rose', 'rose' => 'pink',
            'purple' => 'fuchsia', 'fuchsia' => 'pink', 'pink' => 'rose', 'slate' => 'gray'
        ];
        return $map[$primary] ?? $primary;
    }

    /**
     * متد جادویی تولید پالت کامل Tailwind بر اساس یک نام رنگ
     */
    private function generatePalette($colorName)
    {
        $hex = $this->colorHexMap[$colorName] ?? '#4f46e5';
        $secondary = $this->getSecondaryColor($colorName);

        return [
            'text' => "text-{$colorName}-600",
            'text_dark' => "dark:text-{$colorName}-400",
            'bg' => "bg-{$colorName}-600",
            'bg_hover' => "hover:bg-{$colorName}-700",
            'bg_light' => "bg-{$colorName}-50",
            'bg_light_dark' => "dark:bg-{$colorName}-900/30",
            'border_hover' => "hover:border-{$colorName}-500/50",
            'border' => "border-{$colorName}-600",
            'ring' => "focus:ring-{$colorName}-500",
            'focus_border' => "focus:border-{$colorName}-500",
            'gradient_text' => "from-{$colorName}-500 to-{$secondary}-600",
            'shadow' => "shadow-{$colorName}-500/30",
            'shadow_hover' => "hover:shadow-{$colorName}-500/10",
            'group_hover_text' => "group-hover:text-{$colorName}-600",
            'group_hover_text_dark' => "dark:group-hover:text-{$colorName}-400",
            'group_hover_bg' => "group-hover:bg-{$colorName}-600",
            'blob_1' => "bg-{$colorName}-500/10 dark:bg-{$colorName}-500/20",
            'blob_2' => "bg-{$secondary}-500/10 dark:bg-{$secondary}-500/20",
            'bullet' => "bg-{$colorName}-600",
            'hex' => $hex,
            'name' => $colorName // برای استفاده در منطق‌های خاص
        ];
    }

    /**
     * Bind data to the view.
     */
    public function compose(View $view)
    {
        $globalSettings = Setting::pluck('value', 'key')->toArray();
        $appTheme = $globalSettings['app_theme'] ?? 'default';

        // دیکشنری رنگ‌های پیش‌فرض برای هر قالب (در صورتی که کاربر شخصی‌سازی نکرده باشد)
        $defaultThemeColors = [
            'default' => 'indigo',
            'market' => 'orange',
            'booking' => 'teal',
            'properties' => 'blue'
        ];

        // 💡 دریافت مقادیر رنگی سفارشی که مدیر از پنل تنظیم کرده است (حالا به صورت آرایه برای هر قالب جداست)
        $customColors = isset($globalSettings['theme_colors'])
            ? json_decode($globalSettings['theme_colors'], true)
            : [];

        // رنگ نهایی برای قالبِ در حال اجرا را پیدا می‌کنیم
        $activeColorName = $customColors[$appTheme] ?? $defaultThemeColors[$appTheme] ?? 'indigo';

        // پالت را بر اساس رنگ تولید کرده و به ویو می‌فرستیم
        $view->with('t', $this->generatePalette($activeColorName));
    }
}
