<?php

namespace Modules\Projects\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Projects\App\Http\Models\ProjectRole;
use Modules\Projects\App\Http\Models\ProjectMember;

class ProjectsRoleController extends Controller
{
    public function index()
    {
        $this->authorize('projects.settings.manage');

        $roles = ProjectRole::orderBy('sort_order')->orderBy('id')->get();
        $permissions = ProjectRole::availablePermissions();

        return response()->json([
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('projects.settings.manage');

        $validated = $request->validate([
            'display_name' => 'required|string|max:100',
            'name'         => 'nullable|string|max:50|alpha_dash|unique:projects_roles,name',
            'description'  => 'nullable|string|max:500',
            'color'        => 'required|string|in:purple,amber,blue,emerald,indigo,rose,teal,cyan,orange,gray',
            'icon'         => 'required|string|in:crown,pencil,eye,code,palette,bug,shield,briefcase,user,star',
            'permissions'  => 'nullable|array',
        ]);

        $slug = !empty($validated['name']) 
            ? Str::slug($validated['name'], '_') 
            : Str::slug($validated['display_name'], '_');

        if (empty($slug)) {
            $slug = 'role_' . time();
        }

        // Ensure uniqueness of generated slug
        $count = 1;
        $originalSlug = $slug;
        while (ProjectRole::where('name', $slug)->exists()) {
            $slug = $originalSlug . '_' . $count++;
        }

        $sortOrder = (int) ProjectRole::max('sort_order') + 1;

        $role = ProjectRole::create([
            'name'         => $slug,
            'display_name' => $validated['display_name'],
            'description'  => $validated['description'] ?? null,
            'color'        => $validated['color'],
            'icon'         => $validated['icon'],
            'is_system'    => false,
            'permissions'  => $validated['permissions'] ?? [],
            'sort_order'   => $sortOrder,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'نقش جدید با موفقیت ایجاد شد.',
                'role'    => $role,
            ], 201);
        }

        return back()->with('success', 'نقش جدید با موفقیت ایجاد شد.')->with('active_tab', 'roles');
    }

    public function update(Request $request, ProjectRole $role)
    {
        $this->authorize('projects.settings.manage');

        $validated = $request->validate([
            'display_name' => 'required|string|max:100',
            'description'  => 'nullable|string|max:500',
            'color'        => 'required|string|in:purple,amber,blue,emerald,indigo,rose,teal,cyan,orange,gray',
            'icon'         => 'required|string|in:crown,pencil,eye,code,palette,bug,shield,briefcase,user,star',
            'permissions'  => 'nullable|array',
        ]);

        $data = [
            'display_name' => $validated['display_name'],
            'description'  => $validated['description'] ?? null,
            'color'        => $validated['color'],
            'icon'         => $validated['icon'],
            'permissions'  => $validated['permissions'] ?? [],
        ];

        $role->update($data);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'نقش و دسترسی‌ها با موفقیت ویرایش شدند.',
                'role'    => $role->fresh(),
            ]);
        }

        return back()->with('success', 'نقش و دسترسی‌ها با موفقیت ویرایش شدند.')->with('active_tab', 'roles');
    }

    public function destroy(ProjectRole $role)
    {
        $this->authorize('projects.settings.manage');

        if ($role->is_system) {
            if (request()->wantsJson()) {
                return response()->json(['error' => 'نقش‌های پیش‌فرض سیستم قابل حذف نیستند.'], 403);
            }
            return back()->with('error', 'نقش‌های پیش‌فرض سیستم قابل حذف نیستند.')->with('active_tab', 'roles');
        }

        // Reassign members who have this role to 'viewer'
        ProjectMember::where('role', $role->name)->update(['role' => 'viewer']);

        $roleName = $role->display_name;
        $role->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "نقش «{$roleName}» با موفقیت حذف شد.",
            ]);
        }

        return back()->with('success', "نقش «{$roleName}» با موفقیت حذف شد.")->with('active_tab', 'roles');
    }
}
