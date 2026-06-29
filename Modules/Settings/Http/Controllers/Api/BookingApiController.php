<?php

namespace Modules\Settings\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Booking\Entities\BookingService;
use Modules\Booking\Entities\BookingCategory;
use Modules\Booking\Entities\BookingSetting;
use Modules\Booking\Entities\BookingServiceProvider;
use Modules\Settings\Entities\ApiKey;

class BookingApiController extends Controller
{
    /**
     * Display a listing of booking services.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $isBookingActive = \Nwidart\Modules\Facades\Module::has('Booking') && \Nwidart\Modules\Facades\Module::isEnabled('Booking');
        if (!$isBookingActive) {
            return response()->json([
                'success' => false,
                'message' => 'Booking module is not active.'
            ], 404);
        }

        /** @var ApiKey $apiKey */
        $apiKey = $request->get('authenticated_api_key');

        $query = BookingService::query();

        // 1. Apply Hardcoded Key Filters
        // Status filter
        $serviceStatus = $apiKey->filters['service_status'] ?? 'active';
        if ($serviceStatus === 'active') {
            $query->where('status', BookingService::STATUS_ACTIVE);
        } elseif ($serviceStatus === 'inactive') {
            $query->where('status', BookingService::STATUS_INACTIVE);
        }

        // Allowed categories filter
        if (!empty($apiKey->filters['category_ids'])) {
            $query->whereIn('category_id', $apiKey->filters['category_ids']);
        }

        // 2. Apply Optional Request Filters
        // Category Filter from request
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Status Filter from request
        if ($request->filled('status')) {
            $statusInput = strtoupper($request->status);
            if (in_array($statusInput, [BookingService::STATUS_ACTIVE, BookingService::STATUS_INACTIVE])) {
                $query->where('status', $statusInput);
            }
        }

        // Text Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // 3. Eager Load Relations
        $query->with(['category', 'providers']);

        // 4. Sorting
        $orderBy = $apiKey->filters['order_by'] ?? 'created_at';
        $orderDir = $apiKey->filters['order_direction'] ?? 'desc';
        if (in_array($orderBy, ['created_at', 'updated_at', 'name', 'base_price', 'id'])) {
            $query->orderBy($orderBy, $orderDir);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // 5. Pagination
        $maxPerPage = $apiKey->filters['per_page_max'] ?? 100;
        $perPage = min((int) $request->get('per_page', 15), (int) $maxPerPage);
        if ($perPage < 1) {
            $perPage = 15;
        }

        $services = $query->paginate($perPage);

        // 6. Transform Results
        $items = collect($services->items())->map(function ($service) use ($apiKey) {
            return $this->formatService($service, $apiKey);
        });

        return response()->json([
            'success' => true,
            'data' => $items,
            'meta' => [
                'current_page' => $services->currentPage(),
                'last_page' => $services->lastPage(),
                'per_page' => $services->perPage(),
                'total' => $services->total(),
            ]
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Display the specified booking service.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $idOrSlug
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, string $idOrSlug)
    {
        $isBookingActive = \Nwidart\Modules\Facades\Module::has('Booking') && \Nwidart\Modules\Facades\Module::isEnabled('Booking');
        if (!$isBookingActive) {
            return response()->json([
                'success' => false,
                'message' => 'Booking module is not active.'
            ], 404);
        }

        /** @var ApiKey $apiKey */
        $apiKey = $request->get('authenticated_api_key');

        $query = BookingService::query();

        // Apply Hardcoded Key Filters (consistent with index)
        $serviceStatus = $apiKey->filters['service_status'] ?? 'active';
        if ($serviceStatus === 'active') {
            $query->where('status', BookingService::STATUS_ACTIVE);
        } elseif ($serviceStatus === 'inactive') {
            $query->where('status', BookingService::STATUS_INACTIVE);
        }

        if (!empty($apiKey->filters['category_ids'])) {
            $query->whereIn('category_id', $apiKey->filters['category_ids']);
        }

        $service = $query->with(['category', 'providers'])
            ->where(function ($q) use ($idOrSlug) {
                $q->where('id', $idOrSlug)
                  ->orWhere('slug', $idOrSlug);
            })
            ->first();

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatService($service, $apiKey)
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Format a BookingService model to match the output schema.
     *
     * @param  \Modules\Booking\Entities\BookingService  $service
     * @param  \Modules\Settings\Entities\ApiKey  $apiKey
     * @return array
     */
    private function formatService(BookingService $service, ApiKey $apiKey): array
    {
        $now = now('UTC');
        $base = (float) $service->base_price;
        $discountPrice = $service->discount_price !== null ? (float) $service->discount_price : null;
        $discountFrom = $service->discount_from;
        $discountTo = $service->discount_to;

        $isDiscountActive = $discountPrice !== null;
        if ($isDiscountActive && $discountFrom) {
            $isDiscountActive = $now->gte($discountFrom);
        }
        if ($isDiscountActive && $discountTo) {
            $isDiscountActive = $now->lte($discountTo);
        }

        $effectivePrice = ($isDiscountActive && $discountPrice !== null) ? max(0.0, $discountPrice) : max(0.0, $base);

        // Format Category
        $category = null;
        if ($service->category) {
            $category = [
                'id' => $service->category->id,
                'name' => $service->category->name,
                'slug' => $service->category->slug,
            ];
        }

        // Format Custom Prices & Installments
        $customPricesFormatted = null;
        $customPricesRaw = $service->custom_prices;
        $installmentSettingsRaw = $service->installment_settings;

        if (is_array($customPricesRaw) && isset($customPricesRaw['tabs'])) {
            $customPricesFormatted = ['tabs' => []];
            foreach ($customPricesRaw['tabs'] as $tIdx => $tab) {
                $tabTitle = $tab['title'] ?? 'تب بدون عنوان';
                $sectionsFormatted = [];

                foreach ($tab['sections'] ?? [] as $sIdx => $section) {
                    $sectionTitle = $section['title'] ?? 'بدون عنوان';
                    $sectionType = $section['type'] ?? '';

                    // Get installment settings for this section
                    $sectionInstallmentRaw = $installmentSettingsRaw[$tIdx]['sections'][$sIdx] ?? null;
                    $sectionInstallmentFormatted = null;

                    if ($sectionInstallmentRaw && !empty($sectionInstallmentRaw['is_active'])) {
                        $sectionInstallmentFormatted = [
                            'is_active' => true,
                            'max_months' => isset($sectionInstallmentRaw['max_months']) ? (int) $sectionInstallmentRaw['max_months'] : 3,
                            'down_payment_percent' => isset($sectionInstallmentRaw['down_payment_percent']) ? (float) $sectionInstallmentRaw['down_payment_percent'] : 30,
                            'fee_percent' => isset($sectionInstallmentRaw['fee_percent']) ? (float) $sectionInstallmentRaw['fee_percent'] : 0,
                            'payment_cycle' => $sectionInstallmentRaw['payment_cycle'] ?? 'monthly',
                            'grace_period_days' => isset($sectionInstallmentRaw['grace_period_days']) ? (int) $sectionInstallmentRaw['grace_period_days'] : 3,
                            'late_fee_percent' => isset($sectionInstallmentRaw['late_fee_percent']) ? (float) $sectionInstallmentRaw['late_fee_percent'] : 0,
                        ];
                    }

                    $optionsFormatted = [];
                    foreach ($section['brands'] ?? [] as $bIdx => $brand) {
                        $brandName = $brand['name'] ?? '';
                        $brandPrice = isset($brand['price']) ? (float) $brand['price'] : 0;
                        $brandIsInstallment = !empty($brand['is_installment']);

                        // Get brand level installment override settings if configured
                        $brandInstallmentRaw = $sectionInstallmentRaw['brands'][$bIdx] ?? null;
                        $brandInstallmentFormatted = null;

                        if ($brandInstallmentRaw) {
                            $brandInstallmentFormatted = [
                                'excluded' => !empty($brandInstallmentRaw['excluded']),
                                'max_months' => (isset($brandInstallmentRaw['max_months']) && $brandInstallmentRaw['max_months'] !== '') ? (int) $brandInstallmentRaw['max_months'] : null,
                                'down_payment_percent' => (isset($brandInstallmentRaw['down_payment_percent']) && $brandInstallmentRaw['down_payment_percent'] !== '') ? (float) $brandInstallmentRaw['down_payment_percent'] : null,
                                'fee_percent' => (isset($brandInstallmentRaw['fee_percent']) && $brandInstallmentRaw['fee_percent'] !== '') ? (float) $brandInstallmentRaw['fee_percent'] : null,
                            ];
                        }

                        $optionsFormatted[] = [
                            'name' => $brandName,
                            'price' => $brandPrice,
                            'is_installment' => $brandIsInstallment,
                            'installments' => $brandInstallmentFormatted,
                        ];
                    }

                    $sectionsFormatted[] = [
                        'title' => $sectionTitle,
                        'type' => $sectionType,
                        'installments' => $sectionInstallmentFormatted,
                        'options' => $optionsFormatted,
                    ];
                }

                $customPricesFormatted['tabs'][] = [
                    'title' => $tabTitle,
                    'sections' => $sectionsFormatted,
                ];
            }
        }

        // Format Providers
        $providersFormatted = [];
        $includeProviders = $apiKey->permissions['include_providers'] ?? true;
        if ($includeProviders && $service->providers) {
            foreach ($service->providers as $provider) {
                // Determine effective provider price from pivot
                $pivot = $provider->pivot;
                $provPrice = $base;
                $provDiscountPrice = $discountPrice;
                $provDiscountFrom = $discountFrom;
                $provDiscountTo = $discountTo;

                if ($pivot && $pivot->override_price_mode === BookingServiceProvider::OVERRIDE_MODE_OVERRIDE) {
                    if ($pivot->override_base_price !== null) {
                        $provPrice = (float) $pivot->override_base_price;
                    }
                    if ($pivot->override_discount_price !== null) {
                        $provDiscountPrice = (float) $pivot->override_discount_price;
                    }
                    $provDiscountFrom = $pivot->override_discount_from ?? $provDiscountFrom;
                    $provDiscountTo = $pivot->override_discount_to ?? $provDiscountTo;
                }

                $provDiscountActive = $provDiscountPrice !== null;
                if ($provDiscountActive && $provDiscountFrom) {
                    $provDiscountActive = $now->gte($provDiscountFrom);
                }
                if ($provDiscountActive && $provDiscountTo) {
                    $provDiscountActive = $now->lte($provDiscountTo);
                }

                $provEffectivePrice = ($provDiscountActive && $provDiscountPrice !== null) ? max(0.0, $provDiscountPrice) : max(0.0, $provPrice);

                $providersFormatted[] = [
                    'id' => $provider->id,
                    'name' => $provider->name,
                    'effective_price' => $provEffectivePrice,
                ];
            }
        }

        // Evaluate Online Booking Mode
        $settings = BookingSetting::current();
        $isOnlineBookingEnabled = $settings->global_online_booking_enabled;
        if ($service->online_booking_mode === BookingService::ONLINE_MODE_FORCE_ON) {
            $isOnlineBookingEnabled = true;
        } elseif ($service->online_booking_mode === BookingService::ONLINE_MODE_FORCE_OFF) {
            $isOnlineBookingEnabled = false;
        }

        return [
            'id' => $service->id,
            'name' => $service->name,
            'slug' => $service->slug,
            'status' => $service->status,
            'category' => $category,
            'pricing' => [
                'base_price' => $base,
                'discount_price' => $discountPrice,
                'discount_active' => $isDiscountActive,
                'discount_from' => $discountFrom ? $discountFrom->toIso8601String() : null,
                'discount_to' => $discountTo ? $discountTo->toIso8601String() : null,
                'effective_price' => $effectivePrice,
                'payment_mode' => $service->payment_mode,
                'payment_amount_type' => $service->payment_amount_type,
                'payment_amount_value' => $service->payment_amount_value ? (float) $service->payment_amount_value : null,
            ],
            'custom_prices' => $customPricesFormatted,
            'online_booking' => [
                'enabled' => $isOnlineBookingEnabled,
                'auto_confirm' => (bool) $service->auto_confirm_online_booking,
            ],
            'providers' => $providersFormatted,
            'created_at' => $service->created_at ? $service->created_at->toIso8601String() : null,
            'updated_at' => $service->updated_at ? $service->updated_at->toIso8601String() : null,
        ];
    }
}
