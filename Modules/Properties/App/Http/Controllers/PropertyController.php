<?php

namespace Modules\Properties\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Properties\Entities\Property;
use Modules\Properties\Entities\PropertyAttribute;
use Modules\Properties\Entities\PropertySetting;
use Modules\Properties\Entities\PropertyCategory;
use Modules\Properties\Entities\PropertyBuilding;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        // Start with the visibleToUser scope
        $query = Property::visibleToUser()->with(['status', 'attributeValues', 'category', 'building'])->latest();

        // Check if user has permission to see all properties and requested to do so
        $user = auth()->user();
        if ($user && ($user->hasRole('super-admin') || $user->can('properties.view.all'))) {
            if ($request->has('show_all') && $request->show_all == '1') {
                // Show all: No additional restriction needed as visibleToUser() returns all for admins.
            } else {
                // Default for admins/managers: Restrict to their own properties (Mine only)
                // We need to explicitly apply this restriction because visibleToUser() returns ALL for admins.
                $query->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhere('agent_id', $user->id);
                });
            }
        } else {
            // Guest user or restricted user (Agent)
            // visibleToUser() handles the restriction for Agents.
            // For Guests, we need to ensure only published properties are shown.
            if (!$user) {
                 $query->where('publication_status', 'published');
            }
        }

        $this->applyFilters($query, $request);

        $properties = $query->paginate(12)->withQueryString();
        $showFeatures = PropertySetting::get('show_features_in_card', 1);

        $filterableAttributes = PropertyAttribute::where('is_filterable', true)
            ->where('section', 'details')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $features = PropertyAttribute::where('section', 'features')
            ->where('is_active', true)
            ->where('is_filterable', true)
            ->orderBy('sort_order')
            ->get();

        // Fetch Categories and Buildings for filter
        $categories = PropertyCategory::all();
        $buildings = PropertyBuilding::latest()->get();

        return view('properties::index', compact('properties', 'showFeatures', 'filterableAttributes', 'features', 'categories', 'buildings'));
    }

    public function map(Request $request)
    {
        // Start with the visibleToUser scope
        $query = Property::with(['status', 'attributeValues', 'category', 'building'])->latest();

        // Check if user has permission to see all properties and requested to do so
        $user = auth()->user();
        if ($user && ($user->hasRole('super-admin') || $user->can('properties.view.all'))) {
            if ($request->has('show_all') && $request->show_all == '1') {
                // Show all
            } else {
                // Default for admins/managers: Restrict to their own properties (Mine only)
                $query->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhere('agent_id', $user->id);
                });
            }
        } else {
            // Guest user or restricted user
            if ($user) {
                // User CANNOT see all (e.g. Agent). Always restrict.
                $query->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhere('agent_id', $user->id);
                });
            } else {
                // Guest user
                $query->where('publication_status', 'published');
            }
        }

        // Only properties with coordinates
        $query->whereNotNull('latitude')->whereNotNull('longitude');

        $this->applyFilters($query, $request);

        $properties = $query->get(); // Get all for map (or limit if too many)

        $filterableAttributes = PropertyAttribute::where('is_filterable', true)
            ->where('section', 'details')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $features = PropertyAttribute::where('section', 'features')
            ->where('is_active', true)
            ->where('is_filterable', true)
            ->orderBy('sort_order')
            ->get();

        // Office Location
        $officeLocation = [
            'lat' => PropertySetting::get('office_location_lat'),
            'lng' => PropertySetting::get('office_location_lng'),
            'title' => PropertySetting::get('office_location_title', 'دفتر مرکزی'),
        ];

        return view('properties::map', compact('properties', 'filterableAttributes', 'features', 'officeLocation'));
    }

    private function applyFilters($query, Request $request)
    {
        // 1. جستجوی متنی
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // 2. نوع معامله
        if ($request->filled('listing_type')) {
            $query->where('listing_type', $request->listing_type);
        }

        // 3. نوع ملک
        if ($request->filled('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        // 4. نوع سند
        if ($request->filled('document_type')) {
            $query->where('document_type', $request->document_type);
        }

        // 5. فیلتر قیمت
        $listingType = $request->get('listing_type');

        if ($listingType === 'rent') {
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
        } else {
            if ($request->filled('min_price')) {
                $query->where('price', '>=', $request->min_price);
            }
            if ($request->filled('max_price')) {
                $query->where('price', '<=', $request->max_price);
            }
        }

        // 6. فیلتر متراژ
        if ($request->filled('min_area')) {
            $query->where('area', '>=', $request->min_area);
        }
        if ($request->filled('max_area')) {
            $query->where('area', '<=', $request->max_area);
        }

        // 7. فیلتر ویژگی‌های داینامیک
        $attributes = PropertyAttribute::where('is_filterable', true)
            ->where('is_active', true)
            ->get();

        foreach ($attributes as $attribute) {
            if ($attribute->is_range_filter && $attribute->type === 'number') {
                $minInput = 'min_attr_' . $attribute->id;
                $maxInput = 'max_attr_' . $attribute->id;

                if ($request->filled($minInput) || $request->filled($maxInput)) {
                    $query->whereHas('attributeValues', function ($q) use ($attribute, $request, $minInput, $maxInput) {
                        $q->where('attribute_id', $attribute->id);
                        if ($request->filled($minInput)) {
                            $q->whereRaw('CAST(value AS DECIMAL) >= ?', [$request->input($minInput)]);
                        }
                        if ($request->filled($maxInput)) {
                            $q->whereRaw('CAST(value AS DECIMAL) <= ?', [$request->input($maxInput)]);
                        }
                    });
                }
            } elseif ($attribute->section === 'features') {
                if ($request->filled('features') && is_array($request->features)) {
                    if (in_array($attribute->id, $request->features)) {
                        $query->whereHas('attributeValues', function ($q) use ($attribute) {
                            $q->where('attribute_id', $attribute->id)
                              ->where('value', '1');
                        });
                    }
                }
            } else {
                $inputName = 'attr_' . $attribute->id;
                if ($request->filled($inputName)) {
                    $value = $request->input($inputName);
                    $query->whereHas('attributeValues', function ($q) use ($attribute, $value) {
                        $q->where('attribute_id', $attribute->id);
                        if ($attribute->type === 'text') {
                            $q->where('value', 'like', "%{$value}%");
                        } else {
                            $q->where('value', $value);
                        }
                    });
                }
            }
        }

        // 8. فیلتر املاک ویژه
        if ($request->filled('special') && $request->special == '1') {
            $query->where('meta->is_special', true);
        }

        // 9. Category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // 10. Building
        if ($request->filled('building_id')) {
            $query->where('building_id', $request->building_id);
        }
    }

    public function show($slug)
    {
        Log::info("Show Property Slug: " . $slug);

        if (strlen($slug) > 15) {
            $identifier = substr($slug, 15);
        } else {
            $parts = explode('-', $slug);
            $identifier = end($parts);
        }

        // Added 'building' to eager loading
        $property = Property::with(['status', 'creator', 'attributeValues.attribute', 'images', 'owner', 'category', 'building'])
            ->where(function($query) use ($identifier) {
                $query->where('code', $identifier)
                      ->orWhere('id', $identifier);
            })
            ->first();

        if (!$property) {
            abort(404);
        }

        // Check visibility
        $user = auth()->user();
        if ($user) {
             if (!$user->hasRole('super-admin') && !$user->can('properties.view.all')) {
                 if ($property->created_by !== $user->id && $property->agent_id !== $user->id) {
                     abort(403);
                 }
             }
        } else {
            if ($property->publication_status !== 'published') {
                abort(404);
            }
        }

        // Fetch Visibility Settings
        $visibilitySettings = [
            'owner_info' => json_decode(PropertySetting::get('visibility_owner_info', '[]'), true),
            'confidential_notes' => json_decode(PropertySetting::get('visibility_confidential_notes', '[]'), true),
            'price_info' => json_decode(PropertySetting::get('visibility_price_info', '[]'), true),
            'map_info' => json_decode(PropertySetting::get('visibility_map_info', '[]'), true),
        ];

        return view('properties::show', compact('property', 'visibilitySettings'));
    }
}
