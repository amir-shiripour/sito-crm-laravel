<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Support\PermissionCatalog;
use App\Support\WidgetRegistry;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\WidgetSetting;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::query()
            ->when(
                Schema::hasColumn('roles', 'display_name'),
                fn ($q) => $q->orderBy('display_name')->orderBy('name'),
                fn ($q) => $q->orderBy('name')
            )
            ->get();

        $roleUserCounts = [];
        foreach ($roles as $role) {
            $roleUserCounts[$role->name] = DB::table('model_has_roles')
                ->where('role_id', $role->id)->count();
        }

        return view('admin.roles.index', compact('roles','roleUserCounts'));
    }

    public function create()
    {
        $permissions   = Permission::orderBy('name')->pluck('name')->toArray();
        $permissions_g = Permission::orderBy('name')->get();
        $permissionGroups = PermissionCatalog::groupAndTranslate($permissions_g);

        // 🔹 همه ویجت‌های ثبت‌شده از Registry
        $widgets = WidgetRegistry::all();

        return view('admin.roles.create', compact('permissions','permissionGroups','widgets'));
    }

    private function makeUniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = Str::slug($base);
        if ($slug === '') {
            $slug = Str::slug(Str::ascii($base));
            if ($slug === '') {
                $slug = 'role';
            }
        }

        $original = $slug;
        $i = 2;

        $exists = fn (string $candidate) => Role::where('name', $candidate)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        while ($exists($slug)) {
            $slug = "{$original}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function store(StoreRoleRequest $request)
    {
        $data = $request->validated();

        $slug = $data['name'] ?? null;
        if (!$slug) {
            $slug = $this->makeUniqueSlug($data['display_name'] ?? '');
        }

        $role = Role::create([
            'name'         => $slug,
            'display_name' => $data['display_name'] ?? null,
            'guard_name'   => 'web',
        ]);

        $role->syncPermissions($data['permissions'] ?? []);

        // 🔹 ذخیره تنظیمات ویجت‌ها
        $widgetsInput = $request->input('widgets', []);
        foreach (array_keys($widgetsInput) as $widgetKey) {
            WidgetSetting::create([
                'role_id'    => $role->id,
                'widget_key' => $widgetKey,
                'is_active'  => true,
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')->with('success', 'نقش با موفقیت ایجاد شد.');
    }

    public function edit(Role $role)
    {
        if ($role->name === 'super-admin') {
            // همون تذکر قبلی
        }

        $permissions   = Permission::orderBy('name')->pluck('name')->toArray();
        $permissions_g = Permission::orderBy('name')->get();
        $permissionGroups = PermissionCatalog::groupAndTranslate($permissions_g);
        $selected = $role->permissions()->pluck('name')->toArray();

        // 🔹 همه ویجت‌های موجود
        $widgets = WidgetRegistry::all();

        // 🔹 ویجت‌های فعال برای این نقش
        $roleWidgets = WidgetSetting::where('role_id', $role->id)
            ->where('is_active', true)
            ->pluck('widget_key')
            ->toArray();

        return view('admin.roles.edit', compact(
            'role',
            'permissions',
            'permissionGroups',
            'selected',
            'widgets',
            'roleWidgets'
        ));
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $data = $request->validated();

        $incomingSlug = $data['name'] ?? null;
        if (!$incomingSlug) {
            $incomingSlug = $this->makeUniqueSlug(
                $data['display_name'] ?? $role->display_name ?? $role->name,
                $role->id
            );
        }

        if ($role->name === 'super-admin' && $incomingSlug !== 'super-admin') {
            return back()
                ->withErrors(['name' => 'نقش super-admin قابل تغییر نام نیست.'])
                ->withInput();
        }

        $role->update([
            'name'         => $incomingSlug,
            'display_name' => $data['display_name'] ?? $role->display_name,
        ]);

        if (array_key_exists('permissions', $data)) {
            $role->syncPermissions($data['permissions'] ?? []);
        }

        // 🔹 بروزرسانی ویجت‌ها
        $widgetsInput = $request->input('widgets', []);

        WidgetSetting::where('role_id', $role->id)->delete();

        foreach (array_keys($widgetsInput) as $widgetKey) {
            WidgetSetting::create([
                'role_id'    => $role->id,
                'widget_key' => $widgetKey,
                'is_active'  => true,
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')->with('success', 'نقش با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'super-admin') {
            return back()->withErrors(['role'=>'نقش super-admin قابل حذف نیست.']);
        }

        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')->with('success','نقش حذف شد.');
    }
}
