<?php

namespace Modules\Properties\App\Http\Controllers\User;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Properties\Entities\PropertyCategory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = PropertyCategory::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('properties::user.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'خطا در اعتبارسنجی اطلاعات.');
        }

        PropertyCategory::create([
            'name' => $request->name,
            'slug' => $request->slug ? Str::slug($request->slug) : Str::slug($request->name),
            'color' => $request->color ?? '#6366f1',
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'دسته‌بندی با موفقیت ایجاد شد.');
    }

    public function update(Request $request, PropertyCategory $category)
    {
        if ($category->user_id !== auth()->id()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'خطا در اعتبارسنجی اطلاعات.');
        }

        $category->update([
            'name' => $request->name,
            'slug' => $request->slug ? Str::slug($request->slug) : Str::slug($request->name),
            'color' => $request->color,
        ]);

        return back()->with('success', 'دسته‌بندی با موفقیت ویرایش شد.');
    }

    public function destroy(PropertyCategory $category)
    {
        if ($category->user_id !== auth()->id()) {
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
