<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ExportProjectContext extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sito:export-context';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export Modules architecture, Database schema, and exact WMS state for AI context';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('در حال جمع‌آوری نقشه دقیق پروژه، ساختار دیتابیس جدید و وضعیت نهایی سیستم انبارداری...');

        $data = [
            'installed_packages' => $this->getComposerDependencies(),
            'modules_list' => $this->getDirectoriesList(base_path('Modules')),

            // ساختار پوشه های کلیدی app به عنوان هسته پنل
            'panel_core_structure' => [
                'Controllers' => $this->getDirStructure(app_path('Http/Controllers')),
                'Middleware' => $this->getDirStructure(app_path('Http/Middleware')),
                'Providers' => $this->getDirStructure(app_path('Providers')),
                'View_Components' => $this->getDirStructure(app_path('View/Components')),
            ],

            // ساختار ماژول تنظیمات و مارکت
            'settings_module_structure' => $this->getDirStructure(base_path('Modules/Settings')),
            'market_module_structure' => $this->getDirStructure(base_path('Modules/Market')),
            'global_models' => $this->getDirStructure(app_path('Models')),
            'database_schema' => $this->getDatabaseSchema(),

            // خواندن زنده کدهای انبارداری که توسعه داده‌ای تا ساختار دست‌نخورده بماند
            'wms_current_logic_code' => $this->getWMSCoreCodes(),
        ];

        // ذخیره اطلاعات در پوشه storage
        $path = storage_path('app/sito_crm_context.json');
        File::put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info("نقشه زنده پروژه با موفقیت در مسیر زیر ذخیره شد:");
        $this->line($path);
        $this->info("لطفاً محتوای فایل جدید JSON را کپی کرده و برای من ارسال کنید تا فاز سفارشات را هماهنگ کنیم.");
    }

    /**
     * پکیج‌های نصب شده پروژه را برمی‌گرداند
     */
    private function getComposerDependencies()
    {
        $composerPath = base_path('composer.json');
        if (!File::exists($composerPath)) {
            return 'فایل composer.json پیدا نشد.';
        }

        $composerData = json_decode(File::get($composerPath), true);
        return [
            'require' => $composerData['require'] ?? [],
            'require-dev' => $composerData['require-dev'] ?? []
        ];
    }

    /**
     * گرفتن لیست ماژول‌های موجود
     */
    private function getDirectoriesList($dir)
    {
        if (!File::exists($dir)) return ['پیام' => 'پوشه Modules هنوز ایجاد نشده یا خالی است.'];
        $dirs = File::directories($dir);
        return array_map('basename', $dirs);
    }

    /**
     * گرفتن ساختار درختی فایل‌های یک پوشه
     */
    private function getDirStructure($dir)
    {
        if (!File::exists($dir)) return 'پوشه مورد نظر پیدا نشد.';

        $structure = [];
        $files = File::allFiles($dir);

        foreach ($files as $file) {
            $structure[] = $file->getRelativePathname();
        }

        return $structure;
    }

    /**
     * استخراج کدهای لایه انبارداری برای تسلط کامل روی متدهای توسعه داده شده توسط کاربر
     */
    private function getWMSCoreCodes()
    {
        $coreFiles = [
            'MarketSettings.php' => base_path('Modules/Market/App/Livewire/Admin/MarketSettings.php'),
            'WarehouseStockController.php' => base_path('Modules/Market/App/Livewire/Admin/WarehouseStockController.php'),
            'WarehouseStockService.php' => base_path('Modules/Market/Services/WarehouseStockService.php'),
            'ProductService.php' => base_path('Modules/Market/Services/ProductService.php'),
        ];

        $codes = [];
        foreach ($coreFiles as $key => $path) {
            if (File::exists($path)) {
                $codes[$key] = File::get($path);
            } else {
                // مسیر احتمالی nwidart با ساختار متفاوت
                $alternativePath = base_path('Modules/Market/App/Http/Livewire/Admin/' . $key);
                if (File::exists($alternativePath)) {
                    $codes[$key] = File::get($alternativePath);
                } else {
                    $codes[$key] = "فایل در مسیرهای پیش‌فرض یافت نشد (احتمالاً نام یا مکان آن فرق دارد).";
                }
            }
        }
        return $codes;
    }

    /**
     * استخراج جداول و جزئیات دقیق ستون‌های دیتابیس
     */
    /**
     * استخراج جداول و جزئیات دقیق ستون‌های دیتابیس
     */
    private function getDatabaseSchema()
    {
        $schema = [];
        try {
            $tables = DB::select('SHOW TABLES');
            $tableNames = array_map(function($table) {
                return array_values((array) $table)[0];
            }, $tables);

            foreach ($tableNames as $table) {
                // اصلاح تابع اشتباه و بهینه‌سازی شرط فیلتر جداول
                if (!str_starts_with($table, 'market_') && !in_array($table, ['users', 'settings', 'modules'])) {
                    continue;
                }

                $columns = DB::select("SHOW COLUMNS FROM `{$table}`");
                $schema[$table] = array_map(function($column) {
                    return [
                        'field' => $column->Field,
                        'type' => $column->Type,
                        'null' => $column->Null,
                        'key' => $column->Key,
                    ];
                }, $columns);
            }
        } catch (\Exception $e) {
            $schema['error'] = 'خطا در ارتباط با دیتابیس: ' . $e->getMessage();
        }

        return $schema;
    }
}
