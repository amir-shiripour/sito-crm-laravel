<?php

namespace Modules\Settings\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Properties\Entities\Property;
use Modules\Settings\Entities\ApiKey;

class PropertyApiController extends Controller
{
    /**
     * Display a listing of properties.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        /** @var ApiKey $apiKey */
        $apiKey = $request->get('authenticated_api_key');

        $query = Property::query();

        // 1. Apply Hardcoded Key Filters
        // Publication Status (default: published)
        $pubStatus = $apiKey->filters['publication_status'] ?? 'published';
        if ($pubStatus !== 'all') {
            $query->where('publication_status', $pubStatus);
        }

        // Status IDs
        if (!empty($apiKey->filters['status_ids'])) {
            $query->whereIn('status_id', $apiKey->filters['status_ids']);
        }

        // Require Show in CRM (default: true)
        $requireShowInCrm = $apiKey->filters['require_show_in_crm'] ?? true;
        if ($requireShowInCrm) {
            $query->where('meta->show_on_site', true);
        }

        // Listing Types
        if (!empty($apiKey->filters['listing_types'])) {
            $query->whereIn('listing_type', $apiKey->filters['listing_types']);
        }

        // Property Types
        if (!empty($apiKey->filters['property_types'])) {
            $query->whereIn('property_type', $apiKey->filters['property_types']);
        }

        // 2. Apply Optional Request Filters
        // Text Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Listing Type
        if ($request->filled('listing_type')) {
            $query->where('listing_type', $request->listing_type);
        }

        // Property Type
        if ($request->filled('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        // Category ID
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Status ID
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        // Price Filters
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        if ($request->filled('min_deposit_price')) {
            $query->where('deposit_price', '>=', $request->min_deposit_price);
        }
        if ($request->filled('max_deposit_price')) {
            $query->where('deposit_price', '<=', $request->max_deposit_price);
        }
        if ($request->filled('min_rent_price')) {
            $query->where('rent_price', '>=', $request->min_rent_price);
        }
        if ($request->filled('max_rent_price')) {
            $query->where('rent_price', '<=', $request->max_rent_price);
        }

        // Area Filters
        if ($request->filled('min_area')) {
            $query->where('area', '>=', $request->min_area);
        }
        if ($request->filled('max_area')) {
            $query->where('area', '<=', $request->max_area);
        }

        // 3. Eager Load Relations
        $query->with(['status', 'category', 'building', 'images', 'owner', 'attributeValues.attribute']);

        // 4. Sorting
        $orderBy = $apiKey->filters['order_by'] ?? 'created_at';
        $orderDir = $apiKey->filters['order_direction'] ?? 'desc';
        // Validate orderBy columns to prevent SQL injection
        if (in_array($orderBy, ['created_at', 'updated_at', 'price', 'area', 'id'])) {
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

        $properties = $query->paginate($perPage);

        // 6. Transform Results
        $items = collect($properties->items())->map(function ($property) use ($apiKey) {
            return $this->formatProperty($property, $apiKey);
        });

        return response()->json([
            'success' => true,
            'data' => $items,
            'meta' => [
                'current_page' => $properties->currentPage(),
                'last_page' => $properties->lastPage(),
                'per_page' => $properties->perPage(),
                'total' => $properties->total(),
            ]
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Display the specified property.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $idOrCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, string $idOrCode)
    {
        /** @var ApiKey $apiKey */
        $apiKey = $request->get('authenticated_api_key');

        $query = Property::query();

        // Apply Hardcoded Key Filters (same as index to enforce boundaries)
        $pubStatus = $apiKey->filters['publication_status'] ?? 'published';
        if ($pubStatus !== 'all') {
            $query->where('publication_status', $pubStatus);
        }

        if (!empty($apiKey->filters['status_ids'])) {
            $query->whereIn('status_id', $apiKey->filters['status_ids']);
        }

        $requireShowInCrm = $apiKey->filters['require_show_in_crm'] ?? true;
        if ($requireShowInCrm) {
            $query->where('meta->show_on_site', true);
        }

        if (!empty($apiKey->filters['listing_types'])) {
            $query->whereIn('listing_type', $apiKey->filters['listing_types']);
        }

        if (!empty($apiKey->filters['property_types'])) {
            $query->whereIn('property_type', $apiKey->filters['property_types']);
        }

        // Parse slug / code / ID
        if (strlen($idOrCode) > 15) {
            $identifier = substr($idOrCode, 15);
        } else {
            $parts = explode('-', $idOrCode);
            $identifier = end($parts);
        }

        $property = $query->with(['status', 'category', 'building', 'images', 'owner', 'attributeValues.attribute'])
            ->where(function ($q) use ($identifier, $idOrCode) {
                $q->where('id', $identifier)
                  ->orWhere('code', $identifier)
                  ->orWhere('id', $idOrCode)
                  ->orWhere('code', $idOrCode);
            })
            ->first();

        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatProperty($property, $apiKey)
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Format a Property model to match the output schema.
     *
     * @param  \Modules\Properties\Entities\Property  $property
     * @param  \Modules\Settings\Entities\ApiKey  $apiKey
     * @return array
     */
    private function formatProperty(Property $property, ApiKey $apiKey): array
    {
        // 1. Cover Image URL
        $coverImageUrl = null;
        if ($property->cover_image) {
            $coverImageUrl = str_starts_with($property->cover_image, 'http')
                ? $property->cover_image
                : url('storage/' . $property->cover_image);
        }

        // 2. Images Gallery
        $images = $property->images->map(function ($img) {
            return [
                'url' => str_starts_with($img->path, 'http')
                    ? $img->path
                    : url('storage/' . $img->path),
                'sort_order' => $img->sort_order
            ];
        })->toArray();

        // 3. Dynamic Attributes details & features
        $details = [];
        $features = [];
        foreach ($property->attributeValues as $val) {
            $attr = $val->attribute;
            if (!$attr) {
                continue;
            }

            if ($attr->section === 'details') {
                $details[] = [
                    'id' => $attr->id,
                    'name' => $attr->name,
                    'type' => $attr->type,
                    'value' => $val->value
                ];
            } elseif ($attr->section === 'features' && ($val->value == '1' || $val->value === true)) {
                $features[] = [
                    'id' => $attr->id,
                    'name' => $attr->name,
                    'value' => $val->value
                ];
            }
        }

        // 4. Status mapping
        $status = null;
        if ($property->status) {
            $status = [
                'id' => $property->status->id,
                'key' => $property->status->key,
                'label' => $property->status->label,
                'color' => $property->status->color,
            ];
        }

        // 5. Category mapping
        $category = null;
        if ($property->category) {
            $category = [
                'id' => $property->category->id,
                'name' => $property->category->name,
                'slug' => $property->category->slug,
                'color' => $property->category->color,
            ];
        }

        // 6. Building mapping
        $building = null;
        if ($property->building) {
            $building = [
                'id' => $property->building->id,
                'name' => $property->building->name,
                'address' => $property->building->address,
                'floors_count' => $property->building->floors_count,
                'units_count' => $property->building->units_count,
                'construction_year' => $property->building->construction_year,
            ];
        }

        // 7. Base structure
        $data = [
            'id' => $property->id,
            'title' => $property->title,
            'code' => $property->code,
            'slug' => $property->slug,
            'crm_url' => url('/properties/' . $property->slug),
            'listing_type' => $property->listing_type,
            'property_type' => $property->property_type,
            'usage_type' => $property->usage_type,
            'document_type' => $property->document_type,
            'document_type_label' => Property::DOCUMENT_TYPES[$property->document_type] ?? $property->document_type,
            'publication_status' => $property->publication_status,
            'area' => $property->area,
            'price' => $property->price,
            'min_price' => $property->min_price,
            'deposit_price' => $property->deposit_price,
            'rent_price' => $property->rent_price,
            'advance_price' => $property->advance_price,
            'is_convertible' => (bool)$property->is_convertible,
            'convertible_with' => $property->convertible_with,
            'address' => $property->address,
            'latitude' => $property->latitude ? (float)$property->latitude : null,
            'longitude' => $property->longitude ? (float)$property->longitude : null,
            'delivery_date' => $property->delivery_date ? $property->delivery_date->format('Y-m-d') : null,
            'registered_at' => $property->registered_at ? $property->registered_at->format('Y-m-d') : null,
            'status' => $status,
            'category' => $category,
            'building' => $building,
            'cover_image_url' => $coverImageUrl,
            'images' => $images,
            'video' => $property->video,
            'attributes' => [
                'details' => $details,
                'features' => $features,
            ],
            'meta' => $property->meta,
            'created_at' => $property->created_at ? $property->created_at->toIso8601String() : null,
            'updated_at' => $property->updated_at ? $property->updated_at->toIso8601String() : null,
        ];

        // 8. Sensitive Data under Permission gates
        $includeOwner = $apiKey->permissions['include_owner'] ?? false;
        if ($includeOwner && $property->owner) {
            $data['owner'] = [
                'first_name' => $property->owner->first_name,
                'last_name' => $property->owner->last_name,
                'phone' => $property->owner->phone,
            ];
        }

        $includeConfidential = $apiKey->permissions['include_confidential_notes'] ?? false;
        if ($includeConfidential) {
            $data['confidential_notes'] = $property->confidential_notes;
        }

        return $data;
    }
}
