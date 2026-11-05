<?php

namespace Modules\Properties\App\Http\Controllers\User;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Properties\Entities\PropertyCategory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $canViewAll = $user->hasRole(['super-admin', 'admin']) || $user->can('properties.categories.view') || $user->can('properties.categories.manage');

        $baseQuery = PropertyCategory::query();
        if (!$canViewAll) {
            $baseQuery->where('user_id', $user->id);
        }

        $totalCategories = (clone $baseQuery)->count();
        $categoriesWithProperties = (clone $baseQuery)->has('properties')->count();
        $totalPropertiesCategorized = \Modules\Properties\Entities\Property::whereNotNull('category_id')->count();

        $query = (clone $baseQuery)->withCount('properties');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $categories = $query->latest()->paginate(12)->withQueryString();

        return view('properties::user.categories.index', compact(
            'categories',
            'canViewAll',
            'totalCategories',
            'categoriesWithProperties',
            'totalPropertiesCategorized'
        ));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:100',
            'image' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'خطا در اعتبارسنجی اطلاعات.');
        }

        PropertyCategory::create([
            'name' => $request->name,
            'slug' => $request->slug ? Str::slug($request->slug) : Str::slug($request->name),
            'color' => $request->color ?? '#6366f1',
            'icon' => $request->icon,
            'image' => $request->image,
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'دسته‌بندی با موفقیت ایجاد شد.');
    }

    public function update(Request $request, PropertyCategory $category)
    {
        $user = auth()->user();
        $canManageAll = $user->hasRole(['super-admin', 'admin']) || $user->can('properties.categories.manage');

        if ($category->user_id !== $user->id && !$canManageAll) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:100',
            'image' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'خطا در اعتبارسنجی اطلاعات.');
        }

        $category->update([
            'name' => $request->name,
            'slug' => $request->slug ? Str::slug($request->slug) : Str::slug($request->name),
            'color' => $request->color,
            'icon' => $request->icon,
            'image' => $request->image,
        ]);

        return back()->with('success', 'دسته‌بندی با موفقیت ویرایش شد.');
    }

    public function destroy(PropertyCategory $category)
    {
        $user = auth()->user();
        $canManageAll = $user->hasRole(['super-admin', 'admin']) || $user->can('properties.categories.manage');

        if ($category->user_id !== $user->id && !$canManageAll) {
            abort(403);
        }

        // Check if used in properties?
        // For now, just set null or delete. If set null is needed, we need to handle foreign key constraints or logic.
        // Assuming standard delete is fine, or we can update properties to null.
        $category->properties()->update(['category_id' => null]);

        $category->delete();

        return back()->with('success', 'دسته‌بندی حذف شد.');
    }
}
