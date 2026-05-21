<?php

namespace Modules\Accounting\App\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Accounting\App\Http\Requests\StoreCategoryRequest;
use Modules\Accounting\App\Http\Requests\UpdateCategoryRequest;
use Modules\Accounting\App\Models\Category;
use Modules\Accounting\App\Services\CategoryService;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;

        // Permissions will be set up later
        // $this->middleware('can:accounting.categories.view')->only('index');
        // $this->middleware('can:accounting.categories.create')->only(['create', 'store']);
        // $this->middleware('can:accounting.categories.edit')->only(['edit', 'update']);
        // $this->middleware('can:accounting.categories.delete')->only('destroy');
    }

    public function index()
    {
        $categories = Category::latest()->paginate(15);
        return view('accounting::categories.index', compact('categories'));
    }

    public function create()
    {
        return view('accounting::categories.create');
    }

    public function store(StoreCategoryRequest $request)
    {
        $this->categoryService->createCategory($request->validated());

        return redirect()->route('admin.accounting.categories.index')
            ->with('success', 'دسته بندی با موفقیت ایجاد شد.');
    }

    public function edit(Category $category)
    {
        return view('accounting::categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $this->categoryService->updateCategory($category, $request->validated());

        return redirect()->route('admin.accounting.categories.index')
            ->with('success', 'دسته بندی با موفقیت ویرایش شد.');
    }

    public function destroy(Category $category)
    {
        $this->categoryService->deleteCategory($category);

        return redirect()->route('admin.accounting.categories.index')
            ->with('success', 'دسته بندی با موفقیت حذف شد.');
    }
}
