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
        WidgetRegistry::register('task_summary_widget', [
            'label'      => 'خلاصه وظایف',
            'view'       => 'widgets.task_summary', // بعداً خودت این view رو می‌سازی
            'permission' => null, // یا مثلاً 'tasks.view'
        ]);

        // اینجا هر ویجت هسته‌ای دیگری خواستی اضافه کن
    }
}
