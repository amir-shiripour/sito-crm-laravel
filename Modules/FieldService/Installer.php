<?php

namespace Modules\FieldService;

use App\Models\Module;
use App\Services\Modules\BaseModuleInstaller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Models\CustomUserField;
use Modules\Settings\Entities\Setting;

class Installer extends BaseModuleInstaller
{
    protected string $moduleName = 'FieldService';
    protected string $moduleSlug = 'fieldservice';

    protected array $tables = [
        'field_service_technician_skills',
        'field_service_order_items',
        'field_service_invoices',
        'field_service_backup_requests',
        'field_service_paya_settlements',
        'field_service_ratings',
        'field_service_orders',
        'field_service_building_equipments',
        'field_service_sla_contracts',
        'field_service_buildings',
        'field_service_vendors',
        'field_service_categories',
        'field_service_settings',
        'field_service_technicians',
    ];

    public function __construct()
    {
        parent::__construct($this->moduleName);
    }

    protected function trackerPath(): string
    {
        return storage_path('app/module-installer/' . $this->moduleSlug . '/created.json');
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
        File::put($path, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    public function install(): void
    {
        Log::info('FieldService Installer: Starting install process...');
        parent::install();
        
        $this->ensureModuleDatabaseRecord();
        $this->syncPermissions();
        
        Log::info('FieldService Installer: Install process completed.');
    }

    public function reset(): void
    {
        Log::info('FieldService Installer: Starting reset process...');
        parent::reset();
        
        $this->ensureModuleDatabaseRecord();
        $this->syncPermissions();
        
        Log::info('FieldService Installer: Reset process completed.');
    }

    public function uninstall(): void
    {
        Log::info('FieldService Installer: Starting uninstall process...');
        $this->removeModuleOwnedPermissionsAndRoles();
        parent::uninstall();
        
        // Remove tracker file
        $path = $this->trackerPath();
        if (File::exists($path)) {
            File::delete($path);
        }
        
        Log::info('FieldService Installer: Uninstall process completed.');
    }

    public static function syncModulePermissions(): void
    {
        (new self())->syncPermissions();
    }

    public function syncPermissions(): void
    {
        Log::info('FieldService Installer: Starting permissions sync...');
        $guard = config('auth.defaults.guard', 'web');

        $permissions = [
            'fieldservice.view',
            'fieldservice.manage',
            'fieldservice.fleet.view',
            'fieldservice.fleet.dispatch',
            'fieldservice.orders.view',
            'fieldservice.orders.create',
            'fieldservice.orders.edit',
            'fieldservice.orders.delete',
            'fieldservice.technicians.view',
            'fieldservice.technicians.manage',
            'fieldservice.technicians.verify',
            'fieldservice.b2b.view',
            'fieldservice.b2b.manage',
            'fieldservice.settlements.view',
            'fieldservice.settlements.manage',
            'fieldservice.categories.manage',
            'fieldservice.settings.manage',
        ];

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $tracker = $this->loadTracker();

        foreach ($permissions as $name) {
            $perm = Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => $guard]
            );
            if ($perm->wasRecentlyCreated) {
                $tracker['permissions'][] = $perm->name;
            }
        }

        // Grant all permissions to Super Admin / Admin roles if present
        $adminRoles = Role::whereIn('name', ['admin', 'super-admin', 'Super Admin', 'مدیر سیستم', 'مدیر کل'])->get();
        foreach ($adminRoles as $role) {
            $role->givePermissionTo($permissions);
        }

        // Setup technician role & custom fields
        $this->ensureTechnicianRoleAndFields($guard, $tracker);

        $tracker['permissions'] = array_values(array_unique($tracker['permissions'] ?? []));
        $tracker['roles']       = array_values(array_unique($tracker['roles'] ?? []));
        $this->saveTracker($tracker);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Log::info('FieldService Installer: Permissions and technician role synced successfully.');
    }

    protected function ensureTechnicianRoleAndFields(string $guard, array &$tracker): void
    {
        // 1. Spatie Role
        $techRole = Role::firstOrCreate(
            ['name' => 'technician', 'guard_name' => $guard],
            ['display_name' => 'متخصص و تکنسین فنی']
        );
        if (empty($techRole->display_name)) {
            $techRole->update(['display_name' => 'متخصص و تکنسین فنی']);
        }
        if ($techRole->wasRecentlyCreated) {
            $tracker['roles'][] = $techRole->name;
        }

        // Basic permissions for technician
        $techRole->givePermissionTo([
            'fieldservice.view',
            'fieldservice.orders.view',
            'fieldservice.settlements.view',
        ]);

        // 2. Custom User Fields for Role: technician
        $fields = [
            [
                'field_name'       => 'national_code',
                'label'            => 'کد ملی',
                'field_type'       => 'text',
                'is_required'      => true,
                'show_in_register' => true,
                'show_in_profile'  => true,
                'rules'            => ['string', 'size:10'],
            ],
            [
                'field_name'       => 'shaba_number',
                'label'            => 'شماره شبا (IBAN)',
                'field_type'       => 'text',
                'is_required'      => false,
                'show_in_register' => true,
                'show_in_profile'  => true,
                'rules'            => ['nullable', 'string', 'max:34'],
            ],
            [
                'field_name'       => 'province',
                'label'            => 'استان فعالیت',
                'field_type'       => 'select',
                'is_required'      => true,
                'show_in_register' => true,
                'show_in_profile'  => true,
                'rules'            => ['required', 'string'],
            ],
            [
                'field_name'       => 'city',
                'label'            => 'شهر فعالیت',
                'field_type'       => 'select',
                'is_required'      => true,
                'show_in_register' => true,
                'show_in_profile'  => true,
                'rules'            => ['required', 'string'],
            ],
            [
                'field_name'       => 'address',
                'label'            => 'آدرس کارگاه یا محل سکونت',
                'field_type'       => 'textarea',
                'is_required'      => false,
                'show_in_register' => true,
                'show_in_profile'  => true,
                'rules'            => ['nullable', 'string', 'max:500'],
            ],
            [
                'field_name'       => 'national_card_image',
                'label'            => 'تصویر کارت ملی یا مدرک شناسایی',
                'field_type'       => 'file',
                'is_required'      => false,
                'show_in_register' => true,
                'show_in_profile'  => true,
                'rules'            => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:5120'],
            ],
        ];

        foreach ($fields as $fieldData) {
            CustomUserField::updateOrCreate(
                [
                    'role_name'  => 'technician',
                    'field_name' => $fieldData['field_name'],
                ],
                $fieldData
            );
        }

        // 3. Ensure role registration setting exists
        try {
            $regSetting = Setting::where('key', 'registration')->first();
            $regData = [];
            if ($regSetting && !empty($regSetting->value)) {
                $decoded = is_array($regSetting->value) ? $regSetting->value : json_decode($regSetting->value, true);
                $regData = is_array($decoded) ? $decoded : [];
            }
            if (!isset($regData[$techRole->id])) {
                $regData[$techRole->id] = [
                    'enabled'   => '1',
                    'approval'  => 'manual',
                    'approvers' => [],
                ];
                Setting::updateOrCreate(
                    ['key' => 'registration'],
                    ['value' => json_encode($regData, JSON_UNESCAPED_UNICODE)]
                );
            }
        } catch (\Throwable $e) {
            Log::warning('FieldService Installer: Failed to seed registration setting: ' . $e->getMessage());
        }
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
            Log::error('FieldService Installer: removeModuleOwnedPermissionsAndRoles failed: ' . $e->getMessage());
            throw $e;
        } finally {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    protected function ensureModuleDatabaseRecord(): void
    {
        Module::updateOrCreate(
            ['name' => 'FieldService'],
            [
                'slug'         => 'fieldservice',
                'description'  => 'سامانه هوشمند مدیریت خدمات فنی در محل، اعزام تکنسین و قراردادهای B2B',
                'version'      => '1.0.0',
                'active'       => true,
                'installed'    => true,
                'installed_at' => now(),
                'is_core'      => false,
            ]
        );
    }
}
