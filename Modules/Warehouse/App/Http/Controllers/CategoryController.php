<?php

namespace Modules\Warehouse\App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Warehouse\Entities\WarehouseCategory;

class CategoryController extends Controller
{
    /**
     * Display category tree and management interface.
     */
    public function index()
    {
        $categories = WarehouseCategory::roots()
            ->with(['creator', 'children' => fn($q) => $q->with('creator')->withCount(['items', 'subItems'])])
            ->withCount('items')
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        return view('warehouse::categories.index', compact('categories'));
    }

    /**
     * Store a new category or subcategory.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:warehouse_categories,id',
            'order' => 'nullable|integer',
            'description' => 'nullable|string|max:500',
        ], [
            'name.required' => 'نام دسته الزامی است.',
        ]);

        $validated['is_active'] = true;
        $category = WarehouseCategory::create($validated);

        return back()->with('success', "دسته «{$category->name}» با موفقیت افزوده شد.");
    }

    /**
     * Update an existing category.
     */
    public function update(Request $request, WarehouseCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:warehouse_categories,id|different:id',
            'order' => 'nullable|integer',
            'is_active' => 'sometimes|boolean',
            'description' => 'nullable|string|max:500',
        ], [
            'name.required' => 'نام دسته الزامی است.',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $category->update($validated);

        return back()->with('success', "دسته «{$category->name}» با موفقیت به‌روزرسانی شد.");
    }

    /**
     * Remove a category.
     */
    public function destroy(WarehouseCategory $category)
    {
        if ($category->items()->exists() || $category->subItems()->exists()) {
            return back()->with('error', 'این دسته دارای کالاهای متصل است و نمی‌توان آن را حذف کرد.');
        }

        if ($category->children()->exists()) {
            return back()->with('error', 'ابتدا زیردسته‌های این بخش را حذف یا منتقل کنید.');
        }

        $name = $category->name;
        $category->delete();

        return back()->with('success', "دسته «{$name}» با موفقیت حذف شد.");
    }
}
