<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ExportProjectContext extends Command
{
    protected $signature = 'sito:export-context';
    protected $description = 'Export Modules architecture, Database schema, and exact CRM state for AI context';

    public function handle()
    {
        $this->info('در حال جمع‌آوری نقشه دقیق پروژه (نسخه اصلاح شده برای Clients, ClientCalls, FollowUps)...');

        $data = [
            'installed_packages' => $this->getComposerDependencies(),
            'modules_list' => $this->getDirectoriesList(base_path('Modules')),

            'panel_core_structure' => [
                'Controllers' => $this->getDirStructure(app_path('Http/Controllers')),
                'Middleware' => $this->getDirStructure(app_path('Http/Middleware')),
                'Providers' => $this->getDirStructure(app_path('Providers')),
                'View_Components' => $this->getDirStructure(app_path('View/Components')),
            ],

            // ساختار دقیق ماژول‌های CRM بر اساس نام‌های واقعی پروژه شما
            'client_module_structure' => $this->getDirStructure(base_path('Modules/Clients')),
            'call_module_structure' => $this->getDirStructure(base_path('Modules/ClientCalls')),
            'followup_module_structure' => $this->getDirStructure(base_path('Modules/FollowUps')),

            'global_models' => $this->getDirStructure(app_path('Models')),
            'database_schema' => $this->getDatabaseSchema(),

            // استخراج کدهای لایه منطق ماژول‌های CRM
            'crm_current_logic_code' => $this->getCRMCoreCodes(),
        ];

        $path = storage_path('app/sito_crm_context.json');
        File::put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info("نقشه زنده پروژه با موفقیت در مسیر زیر ذخیره شد:");
        $this->line($path);
    }

    private function getComposerDependencies()
    {
        $composerPath = base_path('composer.json');
        if (!File::exists($composerPath)) return 'فایل composer.json پیدا نشد.';
        $composerData = json_decode(File::get($composerPath), true);
        return [
            'require' => $composerData['require'] ?? [],
            'require-dev' => $composerData['require-dev'] ?? []
        ];
    }

    private function getDirectoriesList($dir)
    {
        if (!File::exists($dir)) return ['پیام' => 'پوشه Modules خالی است.'];
        return array_map('basename', File::directories($dir));
    }

    private function getDirStructure($dir)
    {
        if (!File::exists($dir)) return null;
        $structure = [];
        foreach (File::allFiles($dir) as $file) {
            $structure[] = $file->getRelativePathname();
        }
        return $structure;
    }

    private function getCRMCoreCodes()
    {
        // نام دقیق ماژول‌های استخراج شده از خروجی شما
        $targetModules = ['Clients', 'ClientCalls', 'FollowUps'];
        $codes = [];

        foreach ($targetModules as $module) {
            $modulePath = base_path("Modules/{$module}");
            if (!File::exists($modulePath)) continue;

            $importantDirs = [
                $modulePath . '/App/Models',
                $modulePath . '/Entities',
                $modulePath . '/App/Http/Controllers',
                $modulePath . '/App/Livewire',
            ];

            foreach ($importantDirs as $dir) {
                if (File::exists($dir)) {
                    foreach (File::allFiles($dir) as $file) {
                        if ($file->getExtension() === 'php') {
                            $key = $module . '/' . $file->getRelativePathname();
                            $codes[$key] = File::get($file->getRealPath());
                        }
                    }
                }
            }
        }
        return $codes;
    }

    private function getDatabaseSchema()
    {
        $schema = [];
        try {
            $tables = DB::select('SHOW TABLES');
            $tableNames = array_map(function($table) {
                return array_values((array) $table)[0];
            }, $tables);

            foreach ($tableNames as $table) {
                // فیلتر کاملاً اصلاح شده برای گرفتن clients و tasks و ...
                $isValidTable = str_starts_with($table, 'client_') ||
                    $table === 'clients' ||
                    str_starts_with($table, 'follow') ||
                    $table === 'tasks' ||
                    in_array($table, ['users', 'settings', 'modules']);

                if (!$isValidTable) continue;

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
            $schema['error'] = 'خطا در دیتابیس: ' . $e->getMessage();
        }

        return $schema;
    }
}
