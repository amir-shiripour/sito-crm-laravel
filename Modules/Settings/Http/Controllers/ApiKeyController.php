<?php

namespace Modules\Settings\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\Settings\Entities\ApiKey;
use Modules\Properties\Entities\PropertyStatus;
use Modules\Properties\Entities\PropertyCategory;

class ApiKeyController extends Controller
{
    /**
     * ثبت کلید API جدید
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'module' => 'required|string|in:properties,booking',
            'rate_limit_per_hour' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $module = $request->input('module', 'properties');

        if ($module === 'properties') {
            $isPropertiesActive = \Nwidart\Modules\Facades\Module::has('Properties') && \Nwidart\Modules\Facades\Module::isEnabled('Properties');
            if (!$isPropertiesActive) {
                abort(403, 'امکان ثبت کلید API برای ماژول املاک وجود ندارد.');
            }

            $request->validate([
                'filters.publication_status' => 'nullable|string|in:published,draft,all',
                'filters.require_show_in_crm' => 'nullable|boolean',
                'filters.listing_types' => 'nullable|array',
                'filters.property_types' => 'nullable|array',
                'filters.status_ids' => 'nullable|array',
                'filters.per_page_max' => 'nullable|integer|min:1|max:500',
                'filters.order_by' => 'nullable|string|in:created_at,updated_at,price,area,id',
                'filters.order_direction' => 'nullable|string|in:asc,desc',
                'permissions.include_owner' => 'nullable|boolean',
                'permissions.include_confidential_notes' => 'nullable|boolean',
            ]);

            $filters = $request->input('filters', []);
            $filters['publication_status'] = $filters['publication_status'] ?? 'published';
            $filters['require_show_in_crm'] = filter_var($filters['require_show_in_crm'] ?? true, FILTER_VALIDATE_BOOLEAN);
            $filters['per_page_max'] = isset($filters['per_page_max']) ? (int)$filters['per_page_max'] : 100;
            $filters['order_by'] = $filters['order_by'] ?? 'created_at';
            $filters['order_direction'] = $filters['order_direction'] ?? 'desc';
            $filters['listing_types'] = $filters['listing_types'] ?? [];
            $filters['property_types'] = $filters['property_types'] ?? [];
            $filters['status_ids'] = $filters['status_ids'] ?? [];

            $permissions = $request->input('permissions', []);
            $permissions['include_owner'] = filter_var($permissions['include_owner'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $permissions['include_confidential_notes'] = filter_var($permissions['include_confidential_notes'] ?? false, FILTER_VALIDATE_BOOLEAN);

        } else {
            $isBookingActive = \Nwidart\Modules\Facades\Module::has('Booking') && \Nwidart\Modules\Facades\Module::isEnabled('Booking');
            if (!$isBookingActive) {
                abort(403, 'امکان ثبت کلید API برای ماژول نوبت‌دهی وجود ندارد.');
            }

            $request->validate([
                'filters.service_status' => 'nullable|string|in:active,inactive,all',
                'filters.category_ids' => 'nullable|array',
                'filters.category_ids.*' => 'integer',
                'filters.per_page_max' => 'nullable|integer|min:1|max:500',
                'filters.order_by' => 'nullable|string|in:created_at,updated_at,name,base_price,id',
                'filters.order_direction' => 'nullable|string|in:asc,desc',
                'permissions.include_providers' => 'nullable|boolean',
            ]);

            $filters = $request->input('filters', []);
            $filters['service_status'] = $filters['service_status'] ?? 'active';
            $filters['per_page_max'] = isset($filters['per_page_max']) ? (int)$filters['per_page_max'] : 100;
            $filters['order_by'] = $filters['order_by'] ?? 'created_at';
            $filters['order_direction'] = $filters['order_direction'] ?? 'desc';
            $filters['category_ids'] = $filters['category_ids'] ?? [];

            $permissions = $request->input('permissions', []);
            $permissions['include_providers'] = filter_var($permissions['include_providers'] ?? true, FILTER_VALIDATE_BOOLEAN);
        }

        $key = 'crm_key_' . Str::random(40);
        $docsToken = Str::random(32);

        ApiKey::create([
            'name' => $request->name,
            'key' => $key,
            'docs_token' => $docsToken,
            'module' => $module,
            'filters' => $filters,
            'permissions' => $permissions,
            'rate_limit_per_hour' => $request->rate_limit_per_hour,
            'expires_at' => $request->expires_at,
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        return redirect()->back()->with([
            'success' => 'کلید API با موفقیت ساخته شد.',
            'new_api_key' => $key, // برای نمایش یکبار مصرف به کاربر
        ]);
    }

    /**
     * فعال/غیرفعال کردن کلید API
     */
    public function toggleActive(ApiKey $apiKey)
    {
        $apiKey->update([
            'is_active' => !$apiKey->is_active
        ]);

        return redirect()->back()->with('success', 'وضعیت کلید API تغییر کرد.');
    }

    /**
     * حذف کلید API
     */
    public function destroy(ApiKey $apiKey)
    {
        $apiKey->delete();

        return redirect()->back()->with('success', 'کلید API با موفقیت حذف شد.');
    }

    /**
     * پیش‌نمایش خروجی JSON کلید API
     */
    public function preview(Request $request, ApiKey $apiKey)
    {
        $request->merge(['authenticated_api_key' => $apiKey]);
        
        if ($apiKey->module === 'booking') {
            return app(\Modules\Settings\Http\Controllers\Api\BookingApiController::class)->index($request);
        }

        return app(\Modules\Settings\Http\Controllers\Api\PropertyApiController::class)->index($request);
    }

    /**
     * نمایش صفحه مستندات API (عمومی)
     */
    public function docs($token)
    {
        $apiKey = ApiKey::where('docs_token', $token)->first();

        if (!$apiKey) {
            abort(404, 'مستندات یافت نشد.');
        }

        $statuses = collect();
        $categories = collect();
        $bookingCategories = collect();

        $isPropertiesActive = \Nwidart\Modules\Facades\Module::has('Properties') && \Nwidart\Modules\Facades\Module::isEnabled('Properties');
        $isBookingActive = \Nwidart\Modules\Facades\Module::has('Booking') && \Nwidart\Modules\Facades\Module::isEnabled('Booking');

        if ($apiKey->module === 'booking') {
            if (!$isBookingActive) {
                abort(404, 'ماژول نوبت‌دهی فعال نیست.');
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('booking_categories')) {
                $bookingCategories = \Modules\Booking\Entities\BookingCategory::all();
            }
        } else {
            if (!$isPropertiesActive) {
                abort(404, 'ماژول املاک فعال نیست.');
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('property_statuses')) {
                $statuses = \Modules\Properties\Entities\PropertyStatus::all();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('property_categories')) {
                $categories = \Modules\Properties\Entities\PropertyCategory::all();
            }
        }

        return view('settings::api-keys.docs', compact('apiKey', 'statuses', 'categories', 'bookingCategories', 'isPropertiesActive', 'isBookingActive'));
    }
}
