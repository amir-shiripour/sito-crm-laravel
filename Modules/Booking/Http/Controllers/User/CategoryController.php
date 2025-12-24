<?php

namespace Modules\Booking\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Booking\Entities\BookingCategory;
use Modules\Booking\Entities\BookingSetting;
use Modules\Booking\Services\CategoryService;

class CategoryController extends Controller
{
    public function __construct(protected CategoryService $categories)
    {
    }

    public function index(Request $request)
    {
        $categories = $this->categories->paginate($request->user(), 20);

        return view('booking::user.categories.index', compact('categories'));
    }

    public function create()
    {
        $this->ensureCategoryCreateAllowed();

        return view('booking::user.categories.create');
    }

    public function store(Request $request)
    {
        $this->ensureCategoryCreateAllowed();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([BookingCategory::STATUS_ACTIVE, BookingCategory::STATUS_INACTIVE])],
        ]);

        $this->categories->create($request->user(), $data);

        return redirect()
            ->route('user.booking.categories.index')
            ->with('success', 'دسته‌بندی با موفقیت ایجاد شد.');
    }

    public function edit(BookingCategory $category)
    {
        $this->ensureCategoryEditAllowed();
        $this->ensureCategoryAccess($category);

        return view('booking::user.categories.edit', compact('category'));
    }

    public function update(Request $request, BookingCategory $category)
    {
        $this->ensureCategoryEditAllowed();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([BookingCategory::STATUS_ACTIVE, BookingCategory::STATUS_INACTIVE])],
        ]);

        $this->categories->update($request->user(), $category, $data);

        return redirect()
            ->route('user.booking.categories.index')
            ->with('success', 'دسته‌بندی با موفقیت بروزرسانی شد.');
    }

    public function destroy(Request $request, BookingCategory $category)
    {
        $this->ensureCategoryDeleteAllowed();

        $this->categories->delete($request->user(), $category);

        return redirect()
            ->route('user.booking.categories.index')
            ->with('success', 'دسته‌بندی با موفقیت حذف شد.');
    }

    protected function ensureCategoryCreateAllowed(): void
    {
        $user = auth()->user();
        if (! $user || ! $user->can('booking.categories.create')) {
            abort(403);
        }
    }

    protected function ensureCategoryEditAllowed(): void
    {
        $user = auth()->user();
        if (! $user || ! ($user->can('booking.categories.edit') || $user->can('booking.categories.manage'))) {
            abort(403);
        }
    }

    protected function ensureCategoryDeleteAllowed(): void
    {
        $user = auth()->user();
        if (! $user || ! ($user->can('booking.categories.delete') || $user->can('booking.categories.manage'))) {
            abort(403);
        }
    }

    protected function ensureCategoryAccess(BookingCategory $category): void
    {
        $user = auth()->user();
        $settings = BookingSetting::current();

        if ($settings->category_management_scope === 'OWN' && $user && ! $user->can('booking.categories.manage') && ! $user->hasRole('super-admin')) {
            if ((int) $category->creator_id !== (int) $user->id) {
                abort(403);
            }
        }
    }
}
