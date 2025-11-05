<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::query()->orderBy('name')->get();
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
        return view('admin.roles.create', compact('permissions'));
    }

    public function store(StoreRoleRequest $request)
    {
        $data = $request->validated();

        $role = Role::create([
            'name'       => $data['name'],
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($data['permissions'] ?? []);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')->with('success', 'نقش با موفقیت ایجاد شد.');
    }

    public function edit(Role $role)
    {
        if ($role->name === 'super-admin') {
            // نقش سیستمیک را قابل ویرایش کامل نکنیم (به‌خصوص حذف)
        }

        $permissions = Permission::orderBy('name')->pluck('name')->toArray();
        $selected = $role->permissions()->pluck('name')->toArray();

        return view('admin.roles.edit', compact('role','permissions','selected'));
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $data = $request->validated();

        if ($role->name === 'super-admin' && $data['name'] !== 'super-admin') {
            return back()->withErrors(['name'=>'نقش super-admin قابل تغییر نام نیست.']);
        }

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

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
