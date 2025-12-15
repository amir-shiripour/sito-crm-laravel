<?php

namespace Modules\Booking\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Booking\Entities\BookingCategory;
use Modules\Booking\Entities\BookingSetting;
use Modules\Booking\Services\AuditLogger;

class CategoryController extends Controller
{
    public function __construct(protected AuditLogger $audit)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $settings = BookingSetting::current();

        $q = BookingCategory::query();

        // Ownership scope rule
        if ($settings->category_management_scope === 'OWN' && !$user->can('booking.categories.manage') && !$user->hasRole('super-admin')) {
            $q->where('creator_id', $user->id);
        }

        return response()->json(['data' => $q->orderByDesc('id')->paginate((int) ($request->query('per_page', 50)))]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([BookingCategory::STATUS_ACTIVE, BookingCategory::STATUS_INACTIVE])],
        ]);

        $data['creator_id'] = $user->id;

        $cat = BookingCategory::query()->create($data);

        $this->audit->log('CATEGORY_CREATED', 'booking_categories', $cat->id, null, $cat->toArray());

        return response()->json(['data' => $cat], 201);
    }

    public function update(Request $request, BookingCategory $category)
    {
        $user = $request->user();
        $settings = BookingSetting::current();

        if ($settings->category_management_scope === 'OWN' && !$user->can('booking.categories.manage') && !$user->hasRole('super-admin')) {
            if ((int) $category->creator_id !== (int) $user->id) {
                return response()->json(['message' => 'Access denied (own-only).'], 403);
            }
        }

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', Rule::in([BookingCategory::STATUS_ACTIVE, BookingCategory::STATUS_INACTIVE])],
        ]);

        $before = $category->toArray();
        $category->fill($data);
        $category->save();
        $this->audit->log('CATEGORY_UPDATED', 'booking_categories', $category->id, $before, $category->toArray());

        return response()->json(['data' => $category]);
    }

    public function destroy(Request $request, BookingCategory $category)
    {
        $user = $request->user();
        $settings = BookingSetting::current();

        if ($settings->category_management_scope === 'OWN' && !$user->can('booking.categories.manage') && !$user->hasRole('super-admin')) {
            if ((int) $category->creator_id !== (int) $user->id) {
                return response()->json(['message' => 'Access denied (own-only).'], 403);
            }
        }

        $before = $category->toArray();
        $category->delete();
        $this->audit->log('CATEGORY_DELETED', 'booking_categories', $category->id, $before, null);
        return response()->json(['ok' => true]);
    }
}
