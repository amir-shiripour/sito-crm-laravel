<?php

namespace Modules\Projects\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\FileUploadTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Projects\App\Http\Models\ProjectCategory;
use Modules\Projects\App\Http\Requests\StoreProjectCategoryRequest;

class ProjectCategoryController extends Controller
{
    use FileUploadTrait;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('projects.categories.manage');

        $categories = ProjectCategory::withCount('projects')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('projects::categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('projects.categories.manage');

        return view('projects::categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectCategoryRequest $request): RedirectResponse
    {
        $this->authorize('projects.categories.manage');

        $validated = $request->validated();

        if ($request->hasFile('icon')) {
            $validated['icon'] = $this->uploadFile($request->file('icon'), 'project-categories', 'public');
        } else {
            unset($validated['icon']);
        }

        $category = ProjectCategory::create($validated);

        return redirect()
            ->route('projects.categories.index')
            ->with('success', "دسته‌بندی «{$category->name}» با موفقیت ایجاد شد.");
    }

    /**
     * Display the specified resource.
     */
    public function show(ProjectCategory $category)
    {
        $this->authorize('projects.categories.manage');

        $category->load([
            'projects' => function ($query) {
                $query->with(['client', 'status', 'members.user'])->latest();
            }
        ]);

        return view('projects::categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProjectCategory $category)
    {
        $this->authorize('projects.categories.manage');

        return view('projects::categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreProjectCategoryRequest $request, ProjectCategory $category): RedirectResponse
    {
        $this->authorize('projects.categories.manage');

        $validated = $request->validated();

        if ($request->hasFile('icon')) {
            if ($category->icon) {
                Storage::disk('public')->delete($category->icon);
            }
            $validated['icon'] = $this->uploadFile($request->file('icon'), 'project-categories', 'public');
        } elseif ($request->boolean('remove_icon')) {
            if ($category->icon) {
                Storage::disk('public')->delete($category->icon);
            }
            $validated['icon'] = null;
        } else {
            unset($validated['icon']);
        }
        unset($validated['remove_icon']);

        $category->update($validated);

        return redirect()
            ->route('projects.categories.index')
            ->with('success', "دسته‌بندی «{$category->name}» با موفقیت ویرایش شد.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProjectCategory $category): RedirectResponse
    {
        $this->authorize('projects.categories.manage');

        if ($category->projects()->exists()) {
            return back()->with('error', 'امکان حذف این دسته‌بندی وجود ندارد زیرا پروژه‌هایی به آن متصل هستند.');
        }

        $name = $category->name;

        if ($category->icon) {
            Storage::disk('public')->delete($category->icon);
        }

        $category->delete();

        return redirect()
            ->route('projects.categories.index')
            ->with('success', "دسته‌بندی «{$name}» با موفقیت حذف شد.");
    }
}
