<?php

namespace Modules\Clients;

use App\Services\Modules\BaseModuleInstaller;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Installer extends BaseModuleInstaller
{
    public function __construct()
    {
        parent::__construct('Clients');
    }

    public function install(): void
    {
        parent::install();

        // create permissions and menu permission
        $perms = [
            'clients.view',
            'clients.create',
            'clients.edit',
            'clients.delete',
            'clients.manage',
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }
        $admin = Role::firstOrCreate(['name' => 'super-admin']);
        $admin->givePermissionTo($perms);

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo($perms);

        Log::info("Clients Installer: permissions created and roles updated.");
    }

    protected function truncateModuleTables(): void
    {
        // Truncate only module's tables (be careful with FKs)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        if (Schema::hasTable('clients')) {
            DB::table('clients')->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function uninstall(): void
    {
        // backup handled by parent
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        if (Schema::hasTable('clients')) {
            Schema::drop('clients');
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        parent::uninstall();

        Log::info("Clients Installer: dropped clients table and removed files.");
    }
}
