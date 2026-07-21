<?php

namespace Modules\Services\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Services\App\Http\Models\ServiceCategory;

class ServiceCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('services.view');

        $categories = ServiceCategory::withCount('services')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('services::categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('services.create');

        return view('services::categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('services.create');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:20',
            'icon' => 'nullable|image|mimes:png,jpg,jpeg,gif,svg,webp|max:2048',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('icon')) {
            $validated['icon'] = $request->file('icon')->store('category-icons', 'public');
        } else {
            unset($validated['icon']);
        }

        $category = ServiceCategory::create($validated);

        return redirect()
            ->route('services.categories.index')
            ->with('success', "دسته‌بندی «{$category->name}» با موفقیت ایجاد شد.");
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ServiceCategory $category)
    {
        $this->authorize('services.edit');

        return view('services::categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ServiceCategory $category)
    {
        $this->authorize('services.edit');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:20',
            'icon' => 'nullable|image|mimes:png,jpg,jpeg,gif,svg,webp|max:2048',
            'remove_icon' => 'nullable|boolean',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('icon')) {
            // Replace: delete old file, store new one
            if ($category->icon) {
                Storage::disk('public')->delete($category->icon);
            }
            $validated['icon'] = $request->file('icon')->store('category-icons', 'public');
        } elseif ($request->boolean('remove_icon')) {
            // Remove without replacing
            if ($category->icon) {
                Storage::disk('public')->delete($category->icon);
            }
            $validated['icon'] = null;
        } else {
            // No change to the icon
            unset($validated['icon']);
        }
        unset($validated['remove_icon']);

        $category->update($validated);

        return redirect()
            ->route('services.categories.index')
            ->with('success', "دسته‌بندی «{$category->name}» با موفقیت ویرایش شد.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceCategory $category)
    {
        $this->authorize('services.delete');

        $name = $category->name;

        if ($category->icon) {
            Storage::disk('public')->delete($category->icon);
        }

        $category->delete();

        return redirect()
            ->route('services.categories.index')
            ->with('success', "دسته‌بندی «{$name}» با موفقیت حذف شد.");
    }
}
