<?php

namespace App\Providers;

use App\Support\WidgetRegistry;
use Illuminate\Support\ServiceProvider;

class WidgetServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ویجت‌های هسته (غیر ماژولی)
        WidgetRegistry::register('calendar_widget', [
            'label'      => 'تقویم و رویدادها',
            'view'       => 'widgets.calendar.card',
            'permission' => null,
            'group'      => 'هسته',
        ]);

        // اینجا هر ویجت هسته‌ای دیگری خواستی اضافه کن
    }
}
