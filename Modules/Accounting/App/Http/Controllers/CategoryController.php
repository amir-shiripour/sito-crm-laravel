<?php

namespace Modules\Accounting\App\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Accounting\App\Http\Requests\StoreCategoryRequest;
use Modules\Accounting\App\Http\Requests\UpdateCategoryRequest;
use Modules\Accounting\App\Models\Category;
use Modules\Accounting\App\Services\CategoryService;
use Exception;

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

    public function index(\Illuminate\Http\Request $request)
    {
        $query = Category::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('account_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $categories = $query->orderBy('level', 'asc')->orderBy('id', 'asc')->paginate(15);
        return view('accounting::categories.index', compact('categories'));
    }

    public function create()
    {
        $types = [
            'asset' => 'دارایی',
            'liability' => 'بدهی',
            'equity' => 'سرمایه',
            'income' => 'درآمد',
            'expense' => 'هزینه',
        ];
        return view('accounting::categories.create', compact('types'));
    }

    public function store(StoreCategoryRequest $request)
    {
        $this->categoryService->createCategory($request->validated());

        return redirect()->route('admin.accounting.categories.index')
            ->with('success', 'سرفصل با موفقیت ایجاد شد.');
    }

    public function show(Category $category)
    {
        // This method is intentionally left empty or can be redirected.
        // It exists to satisfy middleware that might be checking for standard resource controller methods.
        return abort(404);
    }

    public function edit(Category $category)
    {
        $types = [
            'asset' => 'دارایی',
            'liability' => 'بدهی',
            'equity' => 'سرمایه',
            'income' => 'درآمد',
            'expense' => 'هزینه',
        ];
        return view('accounting::categories.edit', compact('category', 'types'));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $this->categoryService->updateCategory($category, $request->validated());

        return redirect()->route('admin.accounting.categories.index')
            ->with('success', 'سرفصل با موفقیت ویرایش شد.');
    }

    public function destroy(Category $category)
    {
        try {
            $this->categoryService->deleteCategory($category);
            return redirect()->route('admin.accounting.categories.index')
                ->with('success', 'سرفصل با موفقیت حذف شد.');
        } catch (Exception $e) {
            return redirect()->route('admin.accounting.categories.index')
                ->with('error', $e->getMessage());
        }
    }
}
