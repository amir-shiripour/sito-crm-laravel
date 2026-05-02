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
    protected $description = 'Export Modules architecture, Database schema, and packages for AI context';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('در حال جمع‌آوری نقشه پروژه برای توسعه ماژول Market...');

        $data = [
            'installed_packages' => $this->getComposerDependencies(),
            'modules_list' => $this->getDirectoriesList(base_path('Modules')),
            'market_module_structure' => $this->getDirStructure(base_path('Modules/Market')),
            'global_models' => $this->getDirStructure(app_path('Models')),
            'database_schema' => $this->getDatabaseSchema(),
        ];

        // ذخیره اطلاعات در پوشه storage
        $path = storage_path('app/sito_crm_context.json');
        File::put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info("نقشه پروژه با موفقیت در مسیر زیر ذخیره شد:");
        $this->line($path);
        $this->info("لطفاً محتوای این فایل JSON را کپی کرده و برای من ارسال کنید.");
    }

    /**
     * پکیج‌های نصب شده پروژه را برمی‌گرداند تا AI بداند چه ابزارهایی در دسترس است
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
     * گرفتن ساختار درختی فایل‌های یک پوشه (بدون محتوای کد)
     */
    private function getDirStructure($dir)
    {
        if (!File::exists($dir)) return 'پوشه مورد نظر در مسیر ' . basename($dir) . ' پیدا نشد (شاید هنوز ایجاد نشده است).';

        $structure = [];
        $files = File::allFiles($dir);

        foreach ($files as $file) {
            $structure[] = $file->getRelativePathname();
        }

        return $structure;
    }

    /**
     * استخراج جداول و جزئیات دقیق ستون‌های دیتابیس
     */
    private function getDatabaseSchema()
    {
        $schema = [];
        try {
            // استخراج نام جداول (مستقل از نام دیتابیس)
            $tables = DB::select('SHOW TABLES');
            $tableNames = array_map(function($table) {
                return array_values((array) $table)[0];
            }, $tables);

            foreach ($tableNames as $table) {
                // دریافت جزئیات ستون‌ها (نوع، کلید اصلی/خارجی، Nullable بودن)
                $columns = DB::select("SHOW COLUMNS FROM `{$table}`");

                $schema[$table] = array_map(function($column) {
                    return [
                        'field' => $column->Field,
                        'type' => $column->Type,
                        'null' => $column->Null,
                        'key' => $column->Key, // PRI, MUL, UNI
                    ];
                }, $columns);
            }
        } catch (\Exception $e) {
            $schema['error'] = 'خطا در ارتباط با دیتابیس: ' . $e->getMessage();
        }

        return $schema;
    }
}
