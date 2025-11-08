<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Support\PermissionCatalog;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function index()
    {
        // مرتب‌سازی بر اساس display_name اگر ستون وجود دارد، وگرنه بر اساس name
        $roles = Role::query()
            ->when(
                Schema::hasColumn('roles', 'display_name'),
                fn ($q) => $q->orderBy('display_name')->orderBy('name'),
                fn ($q) => $q->orderBy('name')
            )
            ->get();

        // شمارش کاربران هر نقش (ایمن و قابل‌حمل)
        $roleUserCounts = [];
        foreach ($roles as $role) {
            $roleUserCounts[$role->name] = DB::table('model_has_roles')
                ->where('role_id', $role->id)->count();
        }

        return view('admin.roles.index', compact('roles','roleUserCounts'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('name')->pluck('name')->toArray();
        $permissions_g = Permission::orderBy('name')->get();
        $permissionGroups = PermissionCatalog::groupAndTranslate($permissions_g);
        return view('admin.roles.create', compact('permissions','permissionGroups'));
    }

    /**
     * ساخت اسلاگ یکتا از روی نام فارسی/ورودی کاربر
     */
    private function makeUniqueSlug(string $base, ?int $ignoreId = null): string
    {
        // تبدیل به لاتین
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

        // اگر کاربر آیدی لاتین (name) را خالی گذاشته بود، از display_name بساز
        $slug = $data['name'] ?? null;
        if (!$slug) {
            $slug = $this->makeUniqueSlug($data['display_name'] ?? '');
        }

        $role = Role::create([
            'name'         => $slug,                     // آیدی لاتین
            'display_name' => $data['display_name'] ?? null, // نام فارسی
            'guard_name'   => 'web',
        ]);

        $role->syncPermissions($data['permissions'] ?? []);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')->with('success', 'نقش با موفقیت ایجاد شد.');
    }

    public function edit(Role $role)
    {
        if ($role->name === 'super-admin') {
            // نقش سیستمیک را قابل ویرایش کامل نکنیم (به‌خصوص حذف)
            // (همان رفتار قبلی، فقط یادآوری)
        }

        $permissions = Permission::orderBy('name')->pluck('name')->toArray();
        $permissions_g = Permission::orderBy('name')->get();
        $permissionGroups = PermissionCatalog::groupAndTranslate($permissions_g);
        $selected = $role->permissions()->pluck('name')->toArray();

        return view('admin.roles.edit', compact('role','permissions','permissionGroups','selected'));
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $data = $request->validated();

        // اگر name ارائه نشده، از display_name اسلاگ جدید بساز (با درنظرگرفتن یکتا و نادیده‌گرفتن نقش فعلی)
        $incomingSlug = $data['name'] ?? null;
        if (!$incomingSlug) {
            $incomingSlug = $this->makeUniqueSlug($data['display_name'] ?? $role->display_name ?? $role->name, $role->id);
        }

        // محدودیت super-admin: تغییر نام لاتین آن ممنوع
        if ($role->name === 'super-admin' && $incomingSlug !== 'super-admin') {
            return back()->withErrors(['name' => 'نقش super-admin قابل تغییر نام نیست.'])->withInput();
        }

        $role->update([
            'name'         => $incomingSlug,
            'display_name' => $data['display_name'] ?? $role->display_name,
        ]);

        if (array_key_exists('permissions', $data)) {
            $role->syncPermissions($data['permissions'] ?? []);
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
