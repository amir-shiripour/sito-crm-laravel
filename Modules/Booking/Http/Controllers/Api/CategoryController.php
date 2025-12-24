<?php

namespace Modules\Booking\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Booking\Entities\BookingCategory;
use Modules\Booking\Services\CategoryService;

class CategoryController extends Controller
{
    public function __construct(protected CategoryService $categories)
    {
    }

    public function index(Request $request)
    {
        $perPage = (int) ($request->query('per_page', 50));

        return response()->json([
            'data' => $this->categories->paginate($request->user(), $perPage),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([BookingCategory::STATUS_ACTIVE, BookingCategory::STATUS_INACTIVE])],
        ]);

        $category = $this->categories->create($request->user(), $data);

        return response()->json(['data' => $category], 201);
    }

    public function update(Request $request, BookingCategory $category)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', Rule::in([BookingCategory::STATUS_ACTIVE, BookingCategory::STATUS_INACTIVE])],
        ]);

        $category = $this->categories->update($request->user(), $category, $data);

        return response()->json(['data' => $category]);
    }

    public function destroy(Request $request, BookingCategory $category)
    {
        $this->categories->delete($request->user(), $category);

        return response()->json(['ok' => true]);
    }
}
