<?php

namespace Modules\ContentForge;

use App\Services\Modules\BaseModuleInstaller;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

class Installer extends BaseModuleInstaller
{
    protected array $tables = [
        'content_settings',
        'content_entities',
        'content_categories',
        'content_posts',
        'content_tags',
        'content_post_tag',
        'content_short_links',
        'content_redirects',
        'content_post_revisions',
        'content_comments',
    ];

    protected array $permissions = [
        'content.manage'             => 'مدیریت کلان محتوا (Super Admin)',
        'content.dashboard.view'     => 'مشاهده داشبورد محتوا',
        'content.posts.view'         => 'مشاهده برگه‌ها و نوشته‌ها',
        'content.posts.create'       => 'ایجاد برگه/نوشته',
        'content.posts.edit'         => 'ویرایش برگه/نوشته',
        'content.posts.edit.own'     => 'ویرایش نوشته‌های خود',
        'content.posts.delete'       => 'حذف برگه/نوشته',
        'content.posts.publish'      => 'انتشار برگه/نوشته',
        'content.categories.manage'  => 'مدیریت دسته‌بندی‌ها',
        'content.tags.manage'        => 'مدیریت برچسب‌ها',
        'content.comments.manage'    => 'مدیریت نظرات',
        'content.comments.approve'   => 'تایید نظرات',
        'content.media.upload'       => 'آپلود رسانه',
        'content.entities.manage'    => 'مدیریت موجودیت‌های محتوا',
        'content.settings.manage'    => 'مدیریت تنظیمات محتوا',
        'content.redirects.manage'   => 'مدیریت ریدایرکت‌ها',
        'content.shortlinks.manage'  => 'مدیریت لینک‌های کوتاه',
        'content.seo.manage'         => 'مدیریت SEO صفحات',
    ];

    public function __construct()
    {
        parent::__construct('ContentForge');
    }

    protected function trackerPath(): string
    {
        return storage_path('app/module-installer/'.$this->moduleSlug.'/created.json');
    }

    protected function loadTracker(): array
    {
        $path = $this->trackerPath();
        if (File::exists($path)) {
            return json_decode(File::get($path), true) ?: [];
        }
        return ['permissions' => [], 'roles' => []];
    }

    protected function saveTracker(array $data): void
    {
        $path = $this->trackerPath();
        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
    }

    public function install(): void
    {
        parent::install();

        $guard = config('auth.defaults.guard', 'web');
        $tracker = $this->loadTracker();

        // 1. Create permissions
        foreach ($this->permissions as $name => $displayName) {
            $perm = Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => $guard],
                ['display_name' => $displayName]
            );
            if ($perm->wasRecentlyCreated) {
                $tracker['permissions'][] = $perm->name;
            }
        }

        // 2. Create roles
        $moduleRoles = [
            'content-author' => 'نویسنده محتوا',
            'content-admin' => 'مدیر محتوا'
        ];

        foreach ($moduleRoles as $rname => $displayName) {
            $role = Role::firstOrCreate(
                ['name' => $rname, 'guard_name' => $guard],
                ['display_name' => $displayName]
            );
            if ($role->wasRecentlyCreated) {
                $tracker['roles'][] = $role->name;
            }

            if ($rname === 'content-author') {
                $role->syncPermissions([
                    'content.dashboard.view',
                    'content.posts.view',
                    'content.posts.create',
                    'content.posts.edit.own',
                    'content.media.upload',
                    'content.tags.manage',
                ]);
            } elseif ($rname === 'content-admin') {
                $role->syncPermissions([
                    'content.dashboard.view',
                    'content.posts.view',
                    'content.posts.create',
                    'content.posts.edit',
                    'content.posts.delete',
                    'content.posts.publish',
                    'content.categories.manage',
                    'content.tags.manage',
                    'content.comments.manage',
                    'content.comments.approve',
                    'content.media.upload',
                    'content.redirects.manage',
                    'content.shortlinks.manage',
                    'content.seo.manage',
                ]);
            }
        }

        // 3. Assign permissions to Admin & Super Admin
        foreach (['super-admin', 'admin'] as $roleName) {
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => $guard],
                ['display_name' => $roleName === 'super-admin' ? 'مدیر کل' : 'مدیر']
            );
            $role->givePermissionTo(array_keys($this->permissions));
        }

        $tracker['permissions'] = array_values(array_unique($tracker['permissions']));
        $tracker['roles']       = array_values(array_unique($tracker['roles']));
        $this->saveTracker($tracker);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 4. Seed Default Entity and Default Settings
        $this->seedDefaultEntity();
        $this->seedDefaultSettings();
        $this->publishDefaultThemeFiles();

        Log::info("ContentForge Installer: Permissions, default config and theme files seeded.");
    }

    protected function publishDefaultThemeFiles(): void
    {
        try {
            $destDir = resource_path('views/themes/content');
            
            if (!File::exists($destDir)) {
                File::makeDirectory($destDir, 0755, true);
            }

            $sourceDir = __DIR__ . '/resources/views/web';

            $files = [
                'archive.blade.php',
                'category.blade.php',
                'page.blade.php',
                'post.blade.php',
                'tag.blade.php',
            ];

            foreach ($files as $file) {
                $destFile = $destDir . '/' . $file;
                
                // فقط در صورتی که فایل وجود نداشته باشد آن را ایجاد/کپی می‌کنیم
                if (!File::exists($destFile)) {
                    $sourceFile = $sourceDir . '/' . $file;
                    if (File::exists($sourceFile)) {
                        File::copy($sourceFile, $destFile);
                    } else {
                        // در صورتی که فایل مبدا در وب پیدا نشد، یک فایل پیش‌فرض ساده ایجاد می‌کنیم تا سیستم با خطا مواجه نشود
                        File::put($destFile, "{{-- Default ContentForge {$file} Template --}}");
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error("ContentForge Installer publishDefaultThemeFiles failed: " . $e->getMessage());
        }
    }

    protected function seedDefaultEntity(): void
    {
        try {
            $appName = \Modules\Settings\Entities\Setting::where('key', 'app_name')->value('value') ?? 'سامانه مرکزی';
            \Modules\ContentForge\App\Models\ContentEntity::firstOrCreate(
                ['is_default' => true],
                [
                    'name'       => $appName,
                    'slug'       => 'main',
                    'theme_key'  => 'content',
                    'is_active'  => true,
                    'settings'   => [],
                ]
            );
        } catch (\Throwable $e) {
            Log::error("ContentForge Installer seedDefaultEntity failed: " . $e->getMessage());
        }
    }

    protected function seedDefaultSettings(): void
    {
        try {
            $defaults = [
                'general.posts_per_page'            => '12',
                'general.default_theme_key'         => 'content',
                'general.enable_comments'           => 'true',
                'general.enable_tags'               => 'true',
                'general.reading_time_wpm'          => '200',
                'seo.auto_generate_description'     => 'true',
                'seo.description_length'            => '160',
                'seo.auto_schema_markup'            => 'true',
                'short_link.enabled'                => 'true',
                'short_link.prefix'                 => 's',
                'short_link.code_length'            => '6',
                'editor.default_editor'             => 'tiptap',
                'editor.enable_ai'                  => 'false',
            ];

            foreach ($defaults as $key => $value) {
                // با استفاده از updateOrCreate مطمئن می‌شویم که پیش‌فرض‌ها در صورت ریست یا نصب مجدد بازنویسی/ایجاد می‌شوند
                \Modules\ContentForge\Entities\ContentSetting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }
        } catch (\Throwable $e) {
            Log::error("ContentForge Installer seedDefaultSettings failed: " . $e->getMessage());
        }
    }

    public function uninstall(): void
    {
        $this->removeModuleOwnedPermissionsAndRoles();

        try {
            Schema::disableForeignKeyConstraints();
            foreach ($this->tables as $table) {
                Schema::dropIfExists($table);
            }
            Schema::enableForeignKeyConstraints();

            DB::table('migrations')->where('migration', 'like', '%content_forge%')->delete();
        } catch (\Throwable $e) {
            Log::error("ContentForge Installer DB Cleanup failed: " . $e->getMessage());
        }

        parent::uninstall();
        Log::info("ContentForge Installer: uninstalled, permissions and tables safely removed.");
    }

    protected function removeModuleOwnedPermissionsAndRoles(): void
    {
        $guard = config('auth.defaults.guard', 'web');
        $tracker = $this->loadTracker();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::beginTransaction();
        try {
            foreach ($tracker['roles'] ?? [] as $roleName) {
                if (!$roleName) continue;
                $role = Role::where('name', $roleName)->where('guard_name', $guard)->first();
                if ($role) {
                    $role->permissions()->detach();
                    $role->delete();
                }
            }

            foreach ($tracker['permissions'] ?? [] as $permName) {
                if (!$permName) continue;
                $perm = Permission::where('name', $permName)->where('guard_name', $guard)->first();
                if ($perm) {
                    $perm->roles()->detach();
                    $perm->delete();
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("ContentForge Installer: removeModuleOwnedPermissionsAndRoles failed: ".$e->getMessage());
            throw $e;
        } finally {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        $path = $this->trackerPath();
        if (File::exists($path)) {
            File::delete($path);
        }
    }
}
